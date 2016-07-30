<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\Application;
use AppBundle\Entity\Event as EventEntity;
use AppBundle\Entity\Message;
use AppBundle\Service\SendProcess;
use Ndewez\EventsBundle\Model\Event;
use Psr\Log\NullLogger;

/**
 * Class SendProcessTest.
 */
class SendProcessTest extends \PHPUnit_Framework_TestCase
{
    public function testGetApplications()
    {
        $event = new Event();
        $event
            ->setTitle('title')
            ->setNamespace('namespace')
            ->setPayload('payload')
        ;

        $sendProcess = $this->getSendProcessMock();

        $class = new \ReflectionClass($sendProcess);
        $method = $class->getMethod('getApplications');
        $method->setAccessible(true);

        $actual = $method->invokeArgs($sendProcess, [$event]);
        $this->assertCount(1, $actual);
        $this->assertSame('CODE1', $actual[0]->getCode());
        $this->assertTrue($actual[0]->isActive());
    }

    /**
     * @expectedException \AppBundle\Exception\EventNotFoundException
     */
    public function testGetApplicationsEventNotFound()
    {
        $event = new Event();

        $sendProcess = $this->getSendProcessMock(false);

        $class = new \ReflectionClass($sendProcess);
        $method = $class->getMethod('getApplications');
        $method->setAccessible(true);

        $method->invokeArgs($sendProcess, [$event]);
    }

    public function testGetMissingApplications()
    {
        $event = new Event();
        $message = new Message();
        $message->setPartials(['CODE2']);

        $sendProcess = $this->getSendProcessMock();

        $class = new \ReflectionClass($sendProcess);
        $method = $class->getMethod('getMissingApplications');
        $method->setAccessible(true);

        $actual = $method->invokeArgs($sendProcess, [$event, $message]);
        $this->assertCount(1, $actual);
        $this->assertSame('CODE1', $actual[0]->getCode());
        $this->assertTrue($actual[0]->isActive());
    }

    /**
     * @param bool $returnLoadByCode
     *
     * @return SendProcess
     */
    private function getSendProcessMock($returnLoadByCode = true)
    {
        $application1 = new Application();
        $application1->setCode('CODE1');
        $application2 = new Application();
        $application2
            ->setCode('CODE2')
            ->setActive(false)
        ;

        $entity = new EventEntity();
        $entity
            ->addApplication($application1)
            ->addApplication($application2)
        ;

        $loader = $this->getMockBuilder('AppBundle\Service\EventLoader')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        if ($returnLoadByCode) {
            $loader->method('loadByCode')->willReturn($entity);
        } else {
            $loader->method('loadByCode')->willReturn(null);
        }

        $messageProcess = $this->getMockBuilder('AppBundle\Service\MessageProcess')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        return new SendProcess($loader, $messageProcess, new NullLogger());
    }
}
