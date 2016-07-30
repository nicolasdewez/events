<?php

namespace AppBundle\Service;

use AppBundle\Entity\Message;
use AppBundle\Entity\MessageLog;
use AppBundle\Exception\MessageNotFoundException;
use AppBundle\Exception\WorkflowTransitionException;
use AppBundle\Form\Model\MessageSearch;
use AppBundle\Workflow\MessageWorkflow as Workflow;
use Doctrine\ORM\EntityManager;
use Finite\Context;
use Ndewez\EventsBundle\Model\Event;
use Psr\Log\LoggerInterface;

/**
 * Class MessageManager.
 */
class MessageManager
{
    /** @var EntityManager */
    private $manager;

    /** @var Context */
    private $workflow;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param EntityManager   $manager
     * @param Context         $workflow
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $manager, Context $workflow, LoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->workflow = $workflow;
        $this->logger = $logger;
    }

    /**
     * @param Event $event
     *
     * @return Message
     */
    public function initializeMessage(Event $event)
    {
        $this->logger->debug(sprintf('Initialize message for event "%s".', $event->getTitle()));

        $message = new Message();
        $message
            ->setTitle($event->getTitle())
            ->setNamespace($event->getNamespace())
            ->setPayload($event->getPayload())
            ->setState(Workflow::STATE_READY)
        ;

        $this->manager->persist($message);
        $this->manager->flush();

        $this->logger->info(sprintf('Initialize message %d for event "%s".', $message->getId(), $message->getTitle()));

        $this->addLog($message);

        return $message;
    }

    /**
     * @param Message $message
     * @param string  $transition
     * @param array   $partials
     *
     * @throws WorkflowTransitionException
     */
    public function editMessage(Message $message, $transition, array $partials = [])
    {
        $machine = $this->workflow->getStateMachine($message);
        if (!$machine->can($transition)) {
            throw new WorkflowTransitionException($transition, $message->getState());
        }

        $this->logger->info(sprintf('Apply transition "%s" to message %d.', $transition, $message->getId()), ['partials' => $partials]);

        $message->setPartials($partials);
        $machine->apply($transition);

        $this->manager->flush();
        $this->addLog($message);
    }


    /**
     * @param Message $message
     */
    public function deleteMessage(Message $message)
    {
        $this->manager->remove($message);
        $this->manager->flush();
    }

    /**
     * @param array $ids
     *
     * @return Message[]
     *
     * @throws MessageNotFoundException
     */
    public function loadById(array $ids)
    {
        $this->logger->info(sprintf('Load messages by id "%s"', implode(',', $ids)));

        $messages = $this->manager->getRepository(Message::class)->findByIds($ids);
        if (count($ids) !== count($messages)) {
            throw new MessageNotFoundException(
                sprintf('All ids have not be found. Missing "%s"', implode(', ', $this->findMissingIds($ids, $messages)))
            );
        }

        return $messages;
    }

    /**
     * @return Message[]
     */
    public function loadByStateToResend()
    {
        $this->logger->info('Load messages in state to resend');

        return $this->manager->getRepository(Message::class)->findByStateToResend();
    }

    /**
     * @return Message[]
     */
    public function loadByStateToMissingSend()
    {
        $this->logger->info('Load messages in state to missing send');

        return $this->manager->getRepository(Message::class)->findBy(['state' => Workflow::STATE_PARTIAL_SENT]);
    }

    /**
     * @param MessageSearch $search
     *
     * @return Message []
     */
    public function loadBySearch(MessageSearch $search)
    {
        $limit = MessageSearch::PAGE_LIMIT;
        $offset = ($search->getPage() - 1) * $limit;

        return $this->manager->getRepository(Message::class)->findBy([
                'state' => $search->getState(),
                'title' => $search->getTitle(),
            ], [
                'id' => 'DESC'
            ],
            $limit,
            $offset
        );
    }

    /**
     * @param MessageSearch $search
     *
     * @return int
     */
    public function getTotalPagesBySearch(MessageSearch $search)
    {
        $totalMessages = $this->manager->getRepository(Message::class)->countByTitleAndState(
            $search->getTitle(),
            $search->getState()
        );

        return (int)ceil($totalMessages / MessageSearch::PAGE_LIMIT);
    }

    /**
     * @param Message $message
     */
    private function addLog(Message $message)
    {
        $this->logger->info(sprintf('Add log in message %d with state "%s".', $message->getId(), $message->getState()));

        $log = new MessageLog();
        $log->setState($message->getState());

        $message->addLog($log);

        $this->manager->flush();
    }

    /**
     * @param array     $ids
     * @param Message[] $messages
     *
     * @return array
     */
    private function findMissingIds(array $ids, array $messages)
    {
        $missing = $ids;
        foreach ($messages as $message) {
            unset($missing[array_search($message->getId(), $missing)]);
        }

        return $missing;
    }
}
