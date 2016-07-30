<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Application
 *
 * @ORM\Table
 * @ORM\Entity
 *
 * @UniqueEntity("code")
 */
class Application implements AdminEntityInterface
{
    const TYPE_ASYNC = 'asynchronous';
    const TYPE_SYNC = 'synchronous';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(length=50, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=50)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(min=3, max=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(length=512, nullable=true)
     *
     * @Assert\Length(max=512)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(length=20)
     *
     * @Assert\NotBlank
     */
    private $eventsType;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $active;

    public function __construct()
    {
        $this->active = true;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $code
     *
     * @return Application
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $title
     *
     * @return Application
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
     * @param string $url
     *
     * @return Application
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getEventsType()
    {
        return $this->eventsType;
    }

    /**
     * @param string $eventsType
     *
     * @return Application
     */
    public function setEventsType($eventsType)
    {
        $this->eventsType = $eventsType;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return Application
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @param ExecutionContextInterface $context
     * @param mixed                     $payload
     *
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if (self::TYPE_SYNC === $this->eventsType && null === $this->url) {
            $context->buildViolation('application.url')
                ->atPath('url')
                ->addViolation();
        }
    }
}
