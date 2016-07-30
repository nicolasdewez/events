<?php

namespace AppBundle\Service;

use AppBundle\Entity\Event;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

/**
 * Class EventLoader.
 */
class EventLoader
{
    /** @var EntityManager */
    private $manager;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param EntityManager   $manager
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $manager, LoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->logger = $logger;
    }

    /**
     * @param string $code
     *
     * @return Event
     */
    public function loadByCode($code)
    {
        $event = $this->manager->getRepository(Event::class)->findOneBy(['code' => $code]);
        if (null === $event || !$event->isActive()) {
            $this->logger->debug(sprintf('No event found for code %s', $code));

            return null;
        }

        return $event;
    }
}
