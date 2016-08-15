<?php

namespace AppBundle\Service;

use AppBundle\Entity\Message;
use AppBundle\Exception\WorkflowTransitionException;
use AppBundle\Workflow\MessageWorkflow as Workflow;
use Finite\Context;

/**
 * Class PublishEvent.
 */
class PublishEvent
{
    /** @var MessageManager */
    private $manager;

    /** @var SendProcess */
    private $process;

    /** @var EventDecoder */
    private $decoder;

    /** @var Context */
    private $workflow;

    /**
     * @param MessageManager $manager
     * @param SendProcess    $process
     * @param EventDecoder   $decoder
     * @param Context        $workflow
     */
    public function __construct(MessageManager $manager, SendProcess $process, EventDecoder $decoder, Context $workflow)
    {
        $this->manager = $manager;
        $this->process = $process;
        $this->decoder = $decoder;
        $this->workflow = $workflow;
    }

    /**
     * @param string $content
     */
    public function send($content)
    {
        $event = $this->decoder->decodeByString($content);
        $message = $this->manager->initializeMessage($event);
        $this->process->send($event, $message);
    }

    /**
     * @param Message $message
     *
     * @throws WorkflowTransitionException
     */
    public function resend(Message $message)
    {
        $stateMachine = $this->workflow->getStateMachine($message);
        if (!$stateMachine->can(Workflow::TRANS_RESEND)) {
            throw new WorkflowTransitionException(Workflow::TRANS_RESEND, $message->getState());
        }

        $event = $this->decoder->decodeByMessage($message);
        $this->process->resend($event, $message);
    }


    /**
     * @param Message[] $messages
     */
    public function resendMessages(array $messages)
    {
        foreach ($messages as $message) {
            $this->resend($message);
        }
    }

    /**
     * @param Message[] $messages
     */
    public function resendMissingMessages(array $messages)
    {
        foreach ($messages as $message) {
            $this->resendMissing($message);
        }
    }

    /**
     * @param Message $message
     *
     * @throws WorkflowTransitionException
     */
    public function resendMissing(Message $message)
    {
        $stateMachine = $this->workflow->getStateMachine($message);
        if (!$stateMachine->can(Workflow::TRANS_MISSING_SEND)) {
            throw new WorkflowTransitionException(Workflow::TRANS_MISSING_SEND, $message->getState());
        }

        $event = $this->decoder->decodeByMessage($message);
        $this->process->resendMissing($event, $message);
    }
}
