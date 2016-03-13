<?php

namespace Mongobox\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Feed
 *
 * @ORM\Table(name="core_feed")
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\CoreBundle\Entity\Repository\FeedRepository")
 */
class Feed
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_feed", type="integer")
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
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="boolean")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="boolean")
     */
    private $link;

    /**
     * @var int
     *
     * @ORM\Column(name="maxItems", type="smallint")
     */
    private $maxItems;

    /**
     * @var int
     *
     * @ORM\Column(name="weight", type="smallint")
     */
    private $weight;

    /**
     * @ORM\OneToMany(targetEntity="FeedItem", mappedBy="feed")
     * @ORM\JoinColumn(name="id_item", referencedColumnName="id_item")
     */
    protected $items;


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
     * @return Feed
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
     * Set url
     *
     * @param string $url
     *
     * @return Feed
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }


    /**
     * Set description
     *
     * @param string $description
     *
     * @return Feed
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
     * Set maxItems
     *
     * @param int $maxItems
     *
     * @return Feed
     */
    public function setMaxItems($maxItems)
    {
        $this->maxItems = $maxItems;

        return $this;
    }

    /**
     * Get maxItems
     *
     * @return int
     */
    public function getMaxItems()
    {
        return $this->maxItems;
    }

    /**
     * Get weight
     *
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set weight
     *
     * @param int $weight
     *
     * @return Feed
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get items
     *
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Set Items
     *
     * @param mixed $items
     *
     * @return Feed
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

}

