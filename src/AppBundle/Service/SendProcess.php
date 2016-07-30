<?php

namespace AppBundle\Service;

use AppBundle\Entity\Application;
use AppBundle\Entity\Message;
use AppBundle\Exception\EventNotFoundException;
use Ndewez\EventsBundle\Model\Event;
use Psr\Log\LoggerInterface;

/**
 * Class SendProcess.
 */
class SendProcess
{
    /** @var EventLoader */
    private $loader;

    /** @var MessageProcess */
    private $messageProcess;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param EventLoader     $loader
     * @param MessageProcess  $messageProcess
     * @param LoggerInterface $logger
     */
    public function __construct(EventLoader $loader, MessageProcess $messageProcess, LoggerInterface $logger)
    {
        $this->loader = $loader;
        $this->messageProcess = $messageProcess;
        $this->logger = $logger;
    }

    /**
     * @param Event   $event
     * @param Message $message
     */
    public function send(Event $event, Message $message)
    {
        $this->logger->info(sprintf('Get active applications for message %d.', $message->getId()));
        $applications = $this->getApplications($event);

        $this->logger->info(sprintf('Send event for message %d.', $message->getId()));
        $this->messageProcess->send($event, $applications, $message);
    }

    /**
     * @param Event   $event
     * @param Message $message
     */
    public function resend(Event $event, Message $message)
    {
        $this->logger->info(sprintf('Get active applications for message %d.', $message->getId()));
        $applications = $this->getApplications($event);

        $this->logger->info(sprintf('Resend event for message %d.', $message->getId()));
        $this->messageProcess->resend($event, $applications, $message);
    }

    /**
     * @param Event   $event
     * @param Message $message
     */
    public function resendMissing(Event $event, Message $message)
    {
        $this->logger->info(sprintf('Get missing applications for message %d.', $message->getId()));
        $applications = $this->getMissingApplications($event, $message);

        $this->logger->info(sprintf('Resend event for message %d.', $message->getId()));
        $this->messageProcess->resend($event, $applications, $message, true);
    }

    /**
     * @param Event $event
     *
     * @return Application[]
     *
     * @throws EventNotFoundException
     */
    private function getApplications(Event $event)
    {
        $entity = $this->loader->loadByCode($event->getTitle());
        if (null === $entity) {
            throw new EventNotFoundException(sprintf('Event %s not found.', $event->getTitle()));
        }

        $applications = $entity->getActiveApplications();
        $this->logger->debug(
            sprintf(
                'Get active applications for event "%s" (%d): %d application(s)',
                $entity->getCode(),
                $entity->getId(),
                count($applications)
            )
        );

        return $applications;
    }

    /**
     * @param Event   $event
     * @param Message $message
     *
     * @return Application[]
     */
    private function getMissingApplications(Event $event, Message $message)
    {
        $missing = [];
        $applications = $this->getApplications($event);
        foreach ($applications as $application) {
            if (!in_array($application->getCode(), $message->getPartials(), true)) {
                $missing[] = $application;
            }
        }

        return $missing;
    }
}
