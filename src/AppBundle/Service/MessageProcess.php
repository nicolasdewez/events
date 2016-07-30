<?php

namespace AppBundle\Service;

use AppBundle\Entity\Application;
use AppBundle\Entity\Message;
use AppBundle\Workflow\MessageWorkflow as Workflow;
use Ndewez\EventsBundle\Model\Event;
use Psr\Log\LoggerInterface;

/**
 * Class MessageProcess.
 */
class MessageProcess
{
    /** @var MessageManager */
    private $manager;

    /** @var EventSender */
    private $senderSync;

    /** @var EventSender */
    private $senderAsync;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param MessageManager  $manager
     * @param EventSender     $senderSync
     * @param EventSender     $senderAsync
     * @param LoggerInterface $logger
     */
    public function __construct(MessageManager $manager, EventSender $senderSync, EventSender $senderAsync, LoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->senderSync = $senderSync;
        $this->senderAsync = $senderAsync;
        $this->logger = $logger;
    }

    /**
     * @param Event         $event
     * @param Application[] $applications
     * @param Message       $message
     * @param bool          $missingSend
     */
    public function send(Event $event, array $applications, Message $message, $missingSend = false)
    {
        if (!count($applications)) {
            $this->logger->info(sprintf('No applications for message %d.', $message->getId()));
            $this->manager->editMessage($message, Workflow::TRANS_SEND_NO_APPLICATIONS);

            return;
        }

        // Publish to all applications
        $sent = [];
        foreach ($applications as $application) {
            $this->logger->info(sprintf('Send message %d to application "%s".', $message->getId(), $application->getCode()));
            $publish = $this->sendToApplication($event, $application);
            if (false !== $publish) {
                $sent[] = $application->getCode();
            }
        }

        // Log message
        if (count($applications) === count($sent)) {
            $this->logger->info(sprintf('Message %d sent to all applications.', $message->getId()));
            $this->manager->editMessage($message, Workflow::TRANS_SEND);

            return;
        }

        if (0 === count($sent) && !$missingSend) {
            $this->logger->info(sprintf('Message %d not sent. All applications are in error.', $message->getId()));
            $this->manager->editMessage($message, Workflow::TRANS_SEND_ERROR);

            return;
        }

        $this->logger->info(sprintf('Message %d partial sent.', $message->getId()));
        $this->manager->editMessage($message, Workflow::TRANS_SEND_PARTIAL, array_merge($message->getPartials(), $sent));
    }

    /**
     * @param Event         $event
     * @param Application[] $applications
     * @param Message       $message
     * @param bool          $missingSend
     */
    public function resend(Event $event, array $applications, Message $message, $missingSend = false)
    {
        $this->manager->editMessage($message, Workflow::TRANS_NEW_TRY);
        $this->send($event, $applications, $message, $missingSend);
    }
    /**
     * @param Event       $event
     * @param Application $application
     *
     * @return bool
     */
    private function sendToApplication(Event $event, Application $application)
    {
        if (Application::TYPE_SYNC === $application->getEventsType()) {
            $this->logger->debug(sprintf('Send event "%s" in mode "%s"', $event->getTitle(), Application::TYPE_SYNC));

            return $this->senderSync->send($event, $application->getUrl());
        }

        $this->logger->debug(sprintf('Send event "%s" in mode "%s"', $event->getTitle(), Application::TYPE_ASYNC));

        return $this->senderAsync->send($event, $event->getTitle());
    }
}
