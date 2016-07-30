<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\Event;
use AppBundle\Service\EventLoader;
use Doctrine\ORM\EntityManager;
use Psr\Log\NullLogger;

/**
 * Class EventLoaderTest.
 */
class EventLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadEventInactive()
    {
        $event = new Event();
        $event->setActive(false);

        $loader = new EventLoader($this->getManagerMock($event), new NullLogger());
        $return = $loader->loadByCode('code');

        $this->assertNull($return);
    }

    public function testLoadEventActive()
    {
        $event = new Event();

        $loader = new EventLoader($this->getManagerMock($event), new NullLogger());
        $return = $loader->loadByCode('code');

        $this->assertSame($event, $return);
    }

    public function testLoadEventNotFound()
    {
        $loader = new EventLoader($this->getManagerMock(), new NullLogger());
        $return = $loader->loadByCode('code');

        $this->assertNull($return);
    }

    /**
     * @param Event $event
     *
     * @return EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getManagerMock(Event $event = null)
    {
        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $repository
            ->method('findOneBy')
            ->willReturn($event);

        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $manager
            ->method('getRepository')
            ->willReturn($repository)
        ;

        return $manager;
    }
}
