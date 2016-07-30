<?php

namespace AppBundle\Workflow;

/**
 * Class MessageWorkflow.
 */
final class MessageWorkflow
{
    /** States */
    const STATE_READY = 'ready';
    const STATE_SENT = 'sent';
    const STATE_NO_APPLICATIONS = 'no_applications';
    const STATE_PARTIAL_SENT = 'partial_sent';
    const STATE_ERROR = 'error';

    /** Transitions */
    const TRANS_SEND = 'send';
    const TRANS_SEND_ERROR = 'send_error';
    const TRANS_SEND_PARTIAL = 'send_partial';
    const TRANS_SEND_NO_APPLICATIONS = 'send_no_applications';
    const TRANS_NEW_TRY = 'new_try';
    const TRANS_RESEND = 'resend';
    const TRANS_MISSING_SEND = 'missing_send';
}
