<?php

namespace Mongobox\Bundle\TumblrBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * Mongobox\Bundle\TumblrBundle\Entity\Tumblr
 *
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\TumblrBundle\Entity\Repository\TumblrRepository")
 * @ORM\Table(name="tumblr")
 */
class Tumblr
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id_tumblr;

    /**
     * @ORM\Column(type="text")
     */
    protected $image;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $local_path;

    /**
     * @ORM\Column(type="text")
     */
    protected $text;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @ORM\OneToMany(targetEntity="TumblrVote", mappedBy="tumblr", cascade={"persist"})
     * @ORM\JoinColumn(name="id_tumblr", referencedColumnName="id_tumblr")
     */
    protected $tumblr_vote;

    /**
     * @ORM\ManyToMany(targetEntity="Mongobox\Bundle\GroupBundle\Entity\Group", mappedBy="tumblrs", cascade={"persist"})
     */
    protected $groups;

    /**
     * @ORM\ManyToMany(targetEntity="TumblrTag", mappedBy="tumblrs", cascade={"persist"})
     */
    protected $tags;

    /**
     * Constructor
     */
    public function __construct()
    {
		$this->groups = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

	public function setId($id_tumblr)
    {
        $this->id_tumblr = $id_tumblr;

        return $this;
    }

    public function getId()
    {
        return $this->id_tumblr;
    }

    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setLocalPath($local_path)
    {
        $this->local_path = $local_path;

        return $this;
    }

    public function getLocalPath()
    {
        return $this->local_path;
    }

    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getTumblrVote()
    {
        return $this->tumblr_vote;
    }

    public function getSomme()
    {
        $somme = 0;
        $liste_vote = $this->getTumblrVote();
        foreach($liste_vote as $vote) $somme += $vote->getNote();
        return $somme;
    }
    
    public function getMoyenne()
    {
        if( count($this->tumblr_vote) == 0 ) return 0;
    	return round( $this->getSomme()/count($this->tumblr_vote), 2);
    }

    /**
     * Get id_tumblr
     *
     * @return integer 
     */
    public function getIdTumblr()
    {
        return $this->id_tumblr;
    }

    public function addGroup($group)
    {
    	$this->groups[] = $group;
    	return $this;
    }
    
    public function getGroups()
    {
    	return $this->groups;
    }
    
    public function setGroups($groups)
    {
    	$this->groups = $groups;
    	return $this;
    }


    /**
     *
     * @param TumblrTag $tag
     */
    public function addTag($tag) {
        // var_dump( $tag);exit;
        $tag->addTumblr($this);
        $this->tags[] = $tag;
        return $this;
    }

    /**
     * Fonction to delete tag
     * @param Discussion $discussion
     */
    public function removeTag($tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * @return the $tags
     */
    public function getTags() {
        return $this->tags;
    }

    /**
     * @return the $tags
     */
    public function setTags(ArrayCollection $tags) {
        $this->tags = $tags;
        return $this;
    }

    /**
     * Check if entity has a certain tag
     *
     * @param string $tag the tag system name
     * @return boolean
     */
    public function hasTag($tag)
    {
        foreach($this->tags as $tagElt) {
            if($tagElt->getSystemName() == $tag) {
                return true;
            }
        }

        return false;
    }
}