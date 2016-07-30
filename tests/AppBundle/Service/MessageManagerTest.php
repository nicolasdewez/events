<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\Message;
use AppBundle\Form\Model\MessageSearch;
use AppBundle\Service\MessageManager;
use AppBundle\Workflow\MessageWorkflow as Workflow;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class MessageManagerTest.
 */
class MessageManagerTest extends WebTestCase
{
    public function testFindMissingIdsNoResults()
    {
        $messages = [$this->getMessage(1), $this->getMessage(2)];

        $messageManager = $this->getMessageManagerMock();

        $class = new \ReflectionClass($messageManager);
        $method = $class->getMethod('findMissingIds');
        $method->setAccessible(true);

        $actual = $method->invokeArgs($messageManager, [[1,2], $messages]);
        $this->assertEquals([], $actual);
    }

    public function testFindMissingIds()
    {
        $messages = [$this->getMessage(3), $this->getMessage(2)];

        $messageManager = $this->getMessageManagerMock();

        $class = new \ReflectionClass($messageManager);
        $method = $class->getMethod('findMissingIds');
        $method->setAccessible(true);

        $actual = $method->invokeArgs($messageManager, [[1,2,3], $messages]);
        $this->assertEquals([1], $actual);
    }

    public function testEditMessage()
    {
        $date = new \DateTime();
        $message = new Message();

        $messageManager = $this->getMessageManagerMock();
        $messageManager->editMessage($message, Workflow::TRANS_SEND);

        $this->assertEquals(Workflow::STATE_SENT, $message->getState());
        $this->assertCount(0, $message->getPartials());
        $this->assertSame($date->format('Y-m-d'), $message->getDate()->format('Y-m-d'));
    }

    /**
     * @expectedException \AppBundle\Exception\WorkflowTransitionException
     */
    public function testEditMessageNotPossible()
    {
        $messageManager = $this->getMessageManagerMock(true);
        $messageManager->editMessage(new Message(), Workflow::TRANS_SEND);
    }

    public function testLoadById()
    {
        $manager = $this->getMessageManagerMock();
        $messages = $manager->loadById([1, 2]);
        $this->assertCount(2, $messages);
    }

    /**
     * @expectedException \AppBundle\Exception\MessageNotFoundException
     */
    public function testLoadByIdNotPossible()
    {
        $manager = $this->getMessageManagerMock(false, 1);
        $manager->loadById([1, 2]);
    }

    public function testGetTotalPagesBySearch()
    {
        $manager = $this->getMessageManagerMock(false, 1);
        $actual = $manager->getTotalPagesBySearch(new MessageSearch());
        $this->assertSame(2, $actual);
    }

    /**
     * @param bool $finiteMock
     * @param int  $nbMessagesRepository
     *
     * @return MessageManager
     */
    private function getMessageManagerMock($finiteMock = false, $nbMessagesRepository = 2)
    {
        $repository = $this->getMockBuilder('AppBundle\Repository\MessageRepository')
            ->disableOriginalConstructor()
            ->getMock()
        ;


        $repository->method('findByIds')->willReturn($this->getMessages($nbMessagesRepository));
        $repository->method('countByTitleAndState')->willReturn(32);

        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $manager->method('flush')->willReturn(null);
        $manager->method('getRepository')->willReturn($repository);

        if (false === $finiteMock) {
            // Get container just for Finite context
            $kernel = static::createKernel();
            $kernel->boot();
            $container = $kernel->getContainer();
            $context = $container->get('finite.context');
        } else {
            $machine = $this->getMockBuilder('Finite\StateMachine\StateMachineInterface')
                ->disableOriginalConstructor()
                ->getMock()
            ;

            $machine->method('can')->willReturn(false);

            $context = $this->getMockBuilder('Finite\Context')
                ->disableOriginalConstructor()
                ->getMock()
            ;

            $context->method('getStateMachine')->willReturn($machine);
        }

        $messageManager = new MessageManager($manager, $context, new NullLogger());

        return $messageManager;
    }

    private function getMessages($nbMessages)
    {
        $messages = [];
        for ($i=1; $i<=$nbMessages; $i++) {
            $messages[] = $this->getMessage($i);
        }

        return $messages;
    }

    /**
     * @param int $id
     *
     * @return Message
     */
    private function getMessage($id)
    {
        $message = new Message;

        $reflectionClass = new \ReflectionClass('AppBundle\Entity\Message');

        /** @var \ReflectionProperty $reflectionProperty */
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);

        $reflectionProperty->setValue($message, $id);

        return $message;
    }
}
