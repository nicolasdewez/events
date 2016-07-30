<?php

namespace AppBundle\Service;

use AppBundle\Entity\Message;
use AppBundle\Exception\BadEventException;
use JMS\Serializer\SerializerInterface;
use Ndewez\EventsBundle\Model\Event;
use Psr\Log\LoggerInterface;

/**
 * Class EventDecoder.
 */
class EventDecoder
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param SerializerInterface $serializer
     * @param LoggerInterface     $logger
     */
    public function __construct(SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @param string $string
     *
     * @return Event
     */
    public function decodeByString($string)
    {
        $this->logger->debug(sprintf('Deserialize event: "%s".', $string));

        /** @var Event $event */
        $event = $this->serializer->deserialize($string, 'Ndewez\EventsBundle\Model\Event', 'json');
        $this->checkEvent($event);

        return $event;
    }

    /**
     * @param Message $message
     *
     * @return Event
     */
    public function decodeByMessage(Message $message)
    {
        $this->logger->debug(sprintf('Decode event by message %d.', $message->getId()));

        $event = new Event();
        $event
            ->setTitle($message->getTitle())
            ->setNamespace($message->getNamespace())
            ->setPayload($message->getPayload())
        ;

        $this->checkEvent($event);

        return $event;
    }

    /**
     * @param Event $event
     *
     * @throws BadEventException
     */
    private function checkEvent(Event $event)
    {
        if (null === $event->getTitle() || null === $event->getNamespace() || null === $event->getPayload()) {
            throw new BadEventException('Event is not correctly initialized.');
        }
    }
}
