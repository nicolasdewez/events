<?php

namespace AppBundle\Entity;

use AppBundle\Workflow\MessageWorkflow as Workflow;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Finite\StatefulInterface;

/**
 * Messages
 *
 * @ORM\Table(indexes={
 *     @ORM\Index(name="message_date", columns={"date"}),
 *     @ORM\Index(name="message_title", columns={"title"}),
 *     @ORM\Index(name="message_payload", columns={"payload"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MessageRepository")
 */
class Message implements StatefulInterface
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
     * @var string
     *
     * @ORM\Column
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(length=300)
     */
    private $namespace;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $payload;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     */
    private $state;

    /**
     * Complement for state Workflow::STATE_PARTIAL_SENT
     * List of applications sent
     *
     * @var array
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $partials;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MessageLog", mappedBy="message", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $logs;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->state = Workflow::STATE_READY;
        $this->partials = [];
        $this->logs = new ArrayCollection();
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
     * @param string $title
     *
     * @return Message
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $namespace
     *
     * @return Message
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $payload
     *
     * @return Message
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getFiniteState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @return Message
     */
    public function setFiniteState($state)
    {
        $this->state = $state;

        return $this;
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

    /**
     * @return array
     */
    public function getPartials()
    {
        return $this->partials;
    }

    /**
     * @param array $partials
     *
     * @return Message
     */
    public function setPartials(array $partials)
    {
        $this->partials = $partials;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getLogs()
    {
        $logs = $this->logs->toArray();
        usort($logs, function (MessageLog $a, MessageLog $b) {
            return $a->getId() - $b->getId();
        });

        $this->logs = new ArrayCollection($logs);

        return $this->logs;
    }

    /**
     * @param ArrayCollection $logs
     *
     * @return ArrayCollection
     */
    public function setLogs($logs)
    {
        $this->logs = $logs;

        return $this;
    }

    /**
     * @param MessageLog $log
     *
     * @return Message
     */
    public function addLog(MessageLog $log)
    {
        if ($this->logs->contains($log)) {
            return $this;
        }

        $log->setMessage($this);
        $this->logs->add($log);

        return $this;
    }

    /**
     * @param MessageLog $log
     *
     * @return Message
     */
    public function removeLog(MessageLog $log)
    {
        $this->logs->removeElement($log);

        return $this;
    }
}
