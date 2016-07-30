<?php

namespace AppBundle\Exception;

class WorkflowTransitionException extends WorkflowException
{
    /**
     * @param string $transition
     * @param int    $state
     */
    public function __construct($transition, $state)
    {
        parent::__construct(
            sprintf('Transition %s can\'t be applied. Actual state is %s.', $transition, $state)
        );
    }
}
