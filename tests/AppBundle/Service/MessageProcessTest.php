<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\Application;
use AppBundle\Entity\Message;
use AppBundle\Service\MessageManager;
use AppBundle\Service\MessageProcess;
use AppBundle\Workflow\MessageWorkflow as Workflow;
use Ndewez\EventsBundle\Model\Event;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class MessageProcessTest.
 */
class MessageProcessTest extends WebTestCase
{
    public function testSendNoApplications()
    {
        $message = new Message();

        $process = $this->getProcessMock();
        $process->send(new Event(), [], $message);

        $this->assertSame(Workflow::STATE_NO_APPLICATIONS, $message->getState());
        $this->assertCount(0, $message->getPartials());
    }

    public function testSendOk()
    {
        $message = new Message();

        $process = $this->getProcessMock();
        $process->send(new Event(), [new Application()], $message);

        $this->assertSame(Workflow::STATE_SENT, $message->getState());
        $this->assertCount(0, $message->getPartials());
    }

    public function testSendError()
    {
        $message = new Message();
        $application = new Application();
        $application->setEventsType(Application::TYPE_SYNC);

        $process = $this->getProcessMock(false);
        $process->send(new Event(), [$application], $message);

        $this->assertSame(Workflow::STATE_ERROR, $message->getState());
        $this->assertCount(0, $message->getPartials());
    }

    public function testSendPartials()
    {
        $message = new Message();
        $application1 = new Application();
        $application1->setEventsType(Application::TYPE_SYNC);
        $application2 = new Application();
        $application2
            ->setEventsType(Application::TYPE_ASYNC)
            ->setCode('CODE')
        ;

        $process = $this->getProcessMock(false);
        $process->send(new Event(), [$application1, $application2], $message);

        $this->assertSame(Workflow::STATE_PARTIAL_SENT, $message->getState());
        $this->assertCount(1, $message->getPartials());
        $this->assertEquals(['CODE'], $message->getPartials());
    }

    /**
     * @param bool $syncSendReturn
     * @param bool $aSyncSendReturn
     *
     * @return MessageProcess
     */
    private function getProcessMock($syncSendReturn = true, $aSyncSendReturn = true)
    {
        // Get container just for Finite context
        $kernel = static::createKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $manager->method('flush')->willReturn(null);

        $context = $container->get('finite.context');

        $messageManager = new MessageManager($manager, $context, new NullLogger());

        $senderSync = $this
            ->getMockBuilder('AppBundle\Service\EventSender')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $senderSync->method('send')->willReturn($syncSendReturn);

        $senderAsync = $this
            ->getMockBuilder('AppBundle\Service\EventSender')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $senderAsync->method('send')->willReturn($aSyncSendReturn);

        return new MessageProcess($messageManager, $senderSync, $senderAsync, new NullLogger());
    }
}
