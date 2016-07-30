<?php

namespace Tests\AppBundle\Service;

use AppBundle\Exception\AppException;
use AppBundle\Service\Connector\MockConnector;
use AppBundle\Service\EventSender;
use Ndewez\EventsBundle\Model\Event;
use Psr\Log\NullLogger;

/**
 * Class EventSenderTest.
 */
class EventSenderTest extends \PHPUnit_Framework_TestCase
{
    public function testSend()
    {
        $sender = new EventSender(new MockConnector(), new NullLogger());
        $actual = $sender->send(new Event(), '');
        $this->assertTrue($actual);
    }

    public function testErrorWhileSend()
    {
        $connector = $this->getMockBuilder('AppBundle\Service\Connector\ConnectorInterface')
            ->getMock()
        ;

        $connector
            ->method('send')
            ->willThrowException(new AppException())
        ;

        $sender = new EventSender($connector, new NullLogger());
        $actual = $sender->send(new Event(), '');
        $this->assertFalse($actual);
    }
}
