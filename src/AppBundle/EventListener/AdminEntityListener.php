<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\AdminEntityInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AdminEntityListener.
 */
class AdminEntityListener
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
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        if (null === $this->tokenStorage->getToken()) {
            return;
        }

        $username = $this->tokenStorage->getToken()->getUsername();
        $em = $eventArgs->getEntityManager();
        $unit = $em->getUnitOfWork();

        foreach ($unit->getScheduledEntityInsertions() as $insert) {
            if (!($insert instanceof AdminEntityInterface)) {
                continue;
            }

            $this->logger->info(sprintf('%s creates %s:%d', $username, get_class($insert), $insert->getId()));
        }

        foreach ($unit->getScheduledEntityUpdates() as $update) {
            if (!($update instanceof AdminEntityInterface)) {
                continue;
             }

            $this->logger->info(sprintf('%s updates %s:%d', $username, get_class($update), $update->getId()), ['changes' => $unit->getEntityChangeSet($update)]);
        }
    }
}
