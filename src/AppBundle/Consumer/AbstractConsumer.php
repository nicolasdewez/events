<?php

namespace AppBundle\Consumer;

use AppBundle\Service\Connector\AmqpConnectorInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AbstractConsumer.
 */
abstract class AbstractConsumer
{
    /** @var AmqpConnectorInterface */
    protected $connector;

    /**
     * @param AmqpConnectorInterface $connector
     */
    public function setConnector(AmqpConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    public function run()
    {
        $this->connector->listen(array($this, 'execute'));
    }

    /**
     * @param AMQPMessage $amqpMessage
     */
    abstract public function execute(AMQPMessage $amqpMessage);
}
