<?php

namespace AppBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ConnectorCompilerPass.
 */
class ConnectorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->getParameter('call_mock')) {
            return;
        }

        $mock = $container->getDefinition('app.connector.mock');

        $container->getDefinition('app.sender.event.async')
            ->replaceArgument(0, $mock)
        ;

        $container->getDefinition('app.sender.event.sync')
            ->replaceArgument(0, $mock)
        ;

        $container
            ->getDefinition('app.consumer.abstract')
            ->removeMethodCall('setConnector')
            ->addMethodCall('setConnector', [$mock])
        ;
    }
}
