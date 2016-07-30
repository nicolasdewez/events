<?php

namespace AppBundle\Service;

use AppBundle\Exception\AppException;
use AppBundle\Service\Connector\ConnectorInterface;
use Ndewez\EventsBundle\Model\Event;
use Psr\Log\LoggerInterface;

/**
 * Class EventSender.
 */
class EventSender
{
    /** @var ConnectorInterface */
    private $connector;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param ConnectorInterface $connector
     * @param LoggerInterface    $logger
     */
    public function __construct(ConnectorInterface $connector, LoggerInterface $logger)
    {
        $this->connector = $connector;
        $this->logger = $logger;
    }

    /**
     * @param Event  $event
     * @param string $path
     *
     * @return bool
     */
    public function send(Event $event, $path)
    {
        try {
            $this->logger->debug(sprintf('Send event "%s" with connector "%s"', $event->getTitle(), get_class($this->connector)));
            $this->connector->send($event, $path);
        } catch (AppException $exception) {
            $this->logger->error(sprintf('Error in send event "%s" with connector "%s"', $event->getTitle(), get_class($this->connector)));

            return false;
        }

        return true;
    }
}
