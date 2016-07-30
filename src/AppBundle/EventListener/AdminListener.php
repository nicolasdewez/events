<?php

namespace AppBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AdminListener.
 */
class AdminListener
{
    /** @var LoggerInterface */
    private $logger;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param LoggerInterface       $logger
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (null === $this->tokenStorage->getToken()) {
            return;
        }

        $url = $event->getRequest()->getPathInfo();
        if (!preg_match('#^/(application|event)#', $url)) {
            return;
        }

        $username = $this->tokenStorage->getToken()->getUsername();
        $this->logger->info(sprintf('%s accesses to %s', $username, $url));
    }
}
