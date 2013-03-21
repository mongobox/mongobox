<?php

namespace Mongobox\Bundle\JukeboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dedicaces
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\JukeboxBundle\Entity\Repository\DedicacesRepository")
 */
class Dedicaces
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Videos", inversedBy="Dedicaces")
     * @ORM\JoinColumn(name="video_id", referencedColumnName="id")
     */
    protected $video;

    /**
     * @ORM\ManyToMany(targetEntity="Mongobox\Bundle\GroupBundle\Entity\Group", mappedBy="dedicaces", cascade={"persist"})
     */
    protected $groups;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="string", length=255)
     */
    private $text;

    /**
     * @var boolean
     *
     * @ORM\Column(name="permanant", type="boolean")
     */
    private $permanant;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Dedicaces
     */
    public function setText($text)
    {
        $this->text = $text;
    
        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set permanant
     *
     * @param boolean $permanant
     * @return Dedicaces
     */
    public function setPermanant($permanant)
    {
        $this->permanant = $permanant;
    
        return $this;
    }

    /**
     * Get permanant
     *
     * @return boolean 
     */
    public function getPermanant()
    {
        return $this->permanant;
    }

    /**
     * Set video
     *
     * @param \Mongobox\Bundle\JukeboxBundle\Entity\Videos $video
     * @return Dedicaces
     */
    public function setVideo(\Mongobox\Bundle\JukeboxBundle\Entity\Videos $video = null)
    {
        $this->video = $video;
    
        return $this;
    }

    /**
     * Get video
     *
     * @return \Mongobox\Bundle\JukeboxBundle\Entity\Videos 
     */
    public function getVideo()
    {
        return $this->video;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add groups
     *
     * @param \Mongobox\Bundle\GroupBundle\Entity\Group $groups
     * @return Dedicaces
     */
    public function addGroup(\Mongobox\Bundle\GroupBundle\Entity\Group $groups)
    {
        $this->groups[] = $groups;
    
        return $this;
    }

    /**
     * Remove groups
     *
     * @param \Mongobox\Bundle\GroupBundle\Entity\Group $groups
     */
    public function removeGroup(\Mongobox\Bundle\GroupBundle\Entity\Group $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroups()
    {
        return $this->groups;
    }
}