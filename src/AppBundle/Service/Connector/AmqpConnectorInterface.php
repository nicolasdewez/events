<?php

namespace AppBundle\Service\Connector;

/**
 * Interface AmqpConnectorInterface.
 */
interface AmqpConnectorInterface extends ConnectorInterface
{
    /**
     * @param array $callback
     */
    public function listen(array $callback);
}
