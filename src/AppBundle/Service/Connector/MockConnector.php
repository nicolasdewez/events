<?php

namespace AppBundle\Service\Connector;

use Ndewez\EventsBundle\Model\Event;

/**
 * Class MockConnector.
 */
class MockConnector implements AmqpConnectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function send(Event $event, $path)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function listen(array $callback)
    {
        return;
    }
}
