<?php

namespace AppBundle\Service\Connector;

use Ndewez\EventsBundle\Model\Event;

/**
 * Interface ConnectorInterface.
 */
interface ConnectorInterface
{
    /**
     * @param Event  $event
     * @param string $path
     */
    public function send(Event $event, $path);
}
