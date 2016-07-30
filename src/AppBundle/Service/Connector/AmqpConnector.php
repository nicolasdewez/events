<?php

namespace AppBundle\Service\Connector;

use AppBundle\Exception\ListenException;
use AppBundle\Exception\SendException;
use PhpAmqpLib\Exception\AMQPExceptionInterface;
use JMS\Serializer\SerializerInterface;
use Ndewez\EventsBundle\Model\Event;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AmqpConnector.
 */
class AmqpConnector implements AmqpConnectorInterface
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var AMQPStreamConnection */
    private $connection;

    /** @var string */
    private $queueHost;

    /** @var string */
    private $queuePort;

    /** @var string */
    private $queueUser;

    /** @var string */
    private $queuePassword;

    /**
     * @param SerializerInterface $serializer
     * @param string              $queueHost
     * @param string              $queuePort
     * @param string              $queueUser
     * @param string              $queuePassword
     */
    public function __construct(SerializerInterface $serializer, $queueHost, $queuePort, $queueUser, $queuePassword)
    {
        $this->serializer = $serializer;
        $this->queueHost = $queueHost;
        $this->queuePort = $queuePort;
        $this->queueUser = $queueUser;
        $this->queuePassword = $queuePassword;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Event $event, $path)
    {
        $name = $this->buildName($path);
        $exchange = $name;
        $queue = $name;

        $message = new AMQPMessage(
            $this->serializer->serialize($event, 'json'),
            [
                'content_type' => 'text/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]
        );

        try {
            $connection = $this->getConnection();
            $channel = $connection->channel();

            $channel->queue_declare($queue, false, true, false, false);
            $channel->exchange_declare($exchange, 'direct', false, true, false);
            $channel->queue_bind($queue, $exchange);

            $channel->basic_publish($message, $exchange);

            $channel->close();
        } catch (AMQPExceptionInterface $exception) {
            throw new SendException($exception->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function listen(array $callback)
    {
        $exchange = 'publish';
        $queue = 'publish';
        $consumerTag = 'consumer';

        try {
            $connection = $this->getConnection();
            $channel = $connection->channel();

            $channel->queue_declare($queue, false, true, false, false);
            $channel->exchange_declare($exchange, 'direct', false, true, false);
            $channel->queue_bind($queue, $exchange);

            $channel->basic_consume($queue, $consumerTag, false, false, false, false, $callback);

            register_shutdown_function(
                function () use ($channel) {
                    $channel->close();
                }
            );

            while (count($channel->callbacks)) {
                $channel->wait();
            }
        } catch (AMQPExceptionInterface $exception) {
            throw new ListenException($exception->getMessage());
        }
    }

    /**
     * @return AMQPStreamConnection
     */
    private function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = new AMQPStreamConnection(
                $this->queueHost,
                $this->queuePort,
                $this->queueUser,
                $this->queuePassword,
                '/'
            );
        }

        return $this->connection;
    }

    /**
     * @param string $path
     *
     * @return string mixed
     */
    private function buildName($path)
    {
        return $path;
    }
}
