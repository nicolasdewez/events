<?php

namespace AppBundle\Consumer;

use AppBundle\Exception\AppException;
use AppBundle\Exception\EventNotFoundException;
use AppBundle\Service\EventDecoder;
use AppBundle\Service\MessageManager;
use AppBundle\Service\PublishEvent;
use AppBundle\Service\SendProcess;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

/**
 * Class EventConsumer.
 */
class EventConsumer extends AbstractConsumer
{
    /** @var EventDecoder */
    private $decoder;

    /** @var PublishEvent */
    private $publish;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param EventDecoder    $decoder
     * @param PublishEvent    $publish
     * @param LoggerInterface $logger
     */
    public function __construct(EventDecoder $decoder, PublishEvent $publish, LoggerInterface $logger)
    {
        $this->decoder = $decoder;
        $this->publish = $publish;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(AMQPMessage $amqpMessage)
    {
        // Ack message
        $amqpMessage->delivery_info['channel']->basic_ack($amqpMessage->delivery_info['delivery_tag']);

        // Process
        try {
            $this->publish->send($amqpMessage->getBody());
        } catch (EventNotFoundException $exception) {
            $this->logError($exception, $amqpMessage->getBody());
        } catch (AppException $exception) {
            $this->logError($exception, $amqpMessage->getBody());
        }
    }

    /**
     * @param AppException $exception
     * @param string       $message
     */
    private function logError(AppException $exception, $message)
    {
        $this->logger->error(
            sprintf('Error while sending event by Api : %s', $exception->getMessage()),
            ['message' => $message]
        );
    }
}
