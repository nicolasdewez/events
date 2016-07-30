<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Event
 *
 * @ORM\Table
 * @ORM\Entity
 *
 * @UniqueEntity("code")
 */
class Event implements AdminEntityInterface
{
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
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=255)
     */
    private $code;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Application", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $applications;

    public function __construct()
    {
        $this->active = true;
        $this->applications = new ArrayCollection();
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
     * @return Event
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
     * @param bool $active
     *
     * @return Event
     */
    public function setActive($active)
    {
        $this->active = $active;

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
     * @return ArrayCollection
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * @return Application[]
     */
    public function getActiveApplications()
    {
        $applications = array_filter($this->applications->toArray(), function (Application $application) {
            return $application->isActive();
        });

        return $applications;
    }

    /**
     * @param ArrayCollection $applications
     *
     * @return Event
     */
    public function setApplications($applications)
    {
        $this->applications = $applications;

        return $this;
    }

    /**
     * @param Application $application
     *
     * @return Event
     */
    public function addApplication(Application $application)
    {
        if ($this->applications->contains($application)) {
            return $this;
        }

        $this->applications->add($application);

        return $this;
    }

    /**
     * @param Application $application
     *
     * @return Event
     */
    public function removeApplication(Application $application)
    {
        $this->applications->removeElement($application);

        return $this;
    }
}
