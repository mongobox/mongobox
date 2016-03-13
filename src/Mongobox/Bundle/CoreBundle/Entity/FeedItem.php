<?php

namespace Mongobox\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeedItem
 *
 * @ORM\Table(name="core_feed_items")
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\CoreBundle\Entity\Repository\FeedItemRepository")
 */
class FeedItem
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_item", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pubDate", type="datetime", nullable=true)
     */
    private $pubDate;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=true)
     */
    private $link;

    /**
     * @ORM\ManyToOne(targetEntity="Feed", inversedBy="items")
     * @ORM\JoinColumn(name="id_feed", referencedColumnName="id_feed")
     */
    protected $feed;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return FeedItem
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return FeedItem
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set pubDate
     *
     * @param \DateTime $pubDate
     *
     * @return FeedItem
     */
    public function setPubDate($pubDate)
    {
        $this->pubDate = $pubDate;

        return $this;
    }

    /**
     * Get pubDate
     *
     * @return \DateTime
     */
    public function getPubDate()
    {
        return $this->pubDate;
    }

    /**
     * Set link
     *
     * @param string $link
     *
     * @return FeedItem
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set feed
     *
     * @param Feed $feed
     *
     * @return FeedItem
     */
    public function setFeed($feed)
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * Get feed
     *
     * @return Feed
     */
    public function getFeed()
    {
        return $this->feed;
    }
}

