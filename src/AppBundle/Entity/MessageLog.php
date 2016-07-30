<?php

namespace AppBundle\Entity;

use AppBundle\Workflow\MessageWorkflow as Workflow;
use Doctrine\ORM\Mapping as ORM;
use Finite\StatefulInterface;

/**
 * Messages
 *
 * @ORM\Table(indexes={
 *     @ORM\Index(name="message_log_date", columns={"date"})
 * })
 * @ORM\Entity
 */
class MessageLog
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @var Message
     *
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="logs")
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     */
    private $state;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->state = Workflow::STATE_READY;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime $date
     *
     * @return Message
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param Message $message
     *
     * @return Message
     */
    public function setMessage(Message $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }
}
