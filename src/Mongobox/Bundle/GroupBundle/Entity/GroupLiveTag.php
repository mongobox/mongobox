<?php

namespace Mongobox\Bundle\GroupBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * Mongobox\Bundle\GroupBundle\Entity\GroupLiveTag
 *
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\GroupBundle\Entity\Repository\GroupLiveTagRepository")
 * @ORM\Table(name="group_live_tag")
 */
class GroupLiveTag
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Mongobox\Bundle\GroupBundle\Entity\Group", inversedBy="group_live_tag")
     * @ORM\JoinColumn(name="id_group", referencedColumnName="id")
     */
    protected $group;

    /**
     * @ORM\ManyToOne(targetEntity="\Mongobox\Bundle\JukeboxBundle\Entity\VideoTag", inversedBy="group_live_tag")
     * @ORM\JoinColumn(name="id_tag", referencedColumnName="id")
     */
    protected $video_tag;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $select;

	/**
     * Constructor
     */
    public function __construct() {}

	public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of group.
     *
     * @param  string $group
     * @return \Mongobox\Bundle\GroupBundle\Entity\GroupLiveTag
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get the value of group.
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set the value of video_tag.
     *
     * @param  string $video_tag
     * @return \Mongobox\Bundle\GroupBundle\Entity\GroupLiveTag
     */
    public function setVideoTag($video_tag)
    {
        $this->video_tag = $video_tag;

        return $this;
    }

    /**
     * Get the value of video_tag.
     *
     * @return string
     */
    public function getVideoTag()
    {
        return $this->video_tag;
    }
	
	/**
     * Set the value of select.
     *
     * @param  string $select
     * @return \Mongobox\Bundle\GroupBundle\Entity\GroupLiveTag
     */
    public function setSelect($select)
    {
        $this->select = $select;

        return $this;
    }

    /**
     * Get the value of select.
     *
     * @return string
     */
    public function getSelect()
    {
        return $this->select;
    }
}