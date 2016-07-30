<?php

namespace AppBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MessageSearch.
 */
class MessageSearch
{
    const PAGE_LIMIT = 25;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    private $state;

    /** @var int */
    private $page;

    public function __construct()
    {
        $this->page = 1;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return MessageSearch
     */
    public function setTitle($title)
    {
        $this->title = $title;

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
     *
     * @return MessageSearch
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $page
     *
     * @return MessageSearch
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }
}
