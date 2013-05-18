<?php

namespace Mongoeat\Bundle\VoteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Decision
 *
 * @ORM\Table(name="mongoeat_decision")
 * @ORM\Entity(repositoryClass="Mongoeat\Bundle\VoteBundle\Entity\DecisionRepository")
 */
class Decision
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="\Mongobox\Bundle\GroupBundle\Entity\Group", inversedBy="decisions")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    private $group;

    /**
     * @ORM\OneToMany(targetEntity="Mongoeat\Bundle\VoteBundle\Entity\Vote", mappedBy="decision", cascade={"persist"})
     */
    private $votes;

    public function getNombrePersonne(){
        return count($this->group->getUsers());
    }

    public function getRestaurentTop($pos){
        $rest = array();
        foreach($this->votes as $vote){
            $rest[] = $vote->getRestaurant()->getName();
        }
        $res = array_count_values($rest);
        asort($res,SORT_NUMERIC);
        for($i=1;$i<$pos && current($res) !== FALSE;$i++){
            next($res);
        }
        if($i == $pos && current($res) !== FALSE){
            return key($res);
        }
        else
            return;
    }

    public function getRestaurentScore(){
        $rest = array();
        foreach($this->votes as $vote){
            $rest[] = $vote->getRestaurant()->getName();
        }
        $res = array_count_values($rest);
        asort($res,SORT_NUMERIC);
        return $res;
    }
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
     * Set date
     *
     * @param \DateTime $date
     * @return Decision
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->votes = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set group
     *
     * @param \Mongobox\Bundle\GroupBundle\Entity\Group $group
     * @return Decision
     */
    public function setGroup(\Mongobox\Bundle\GroupBundle\Entity\Group $group = null)
    {
        $this->group = $group;
    
        return $this;
    }

    /**
     * Get group
     *
     * @return \Mongobox\Bundle\GroupBundle\Entity\Group 
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Add votes
     *
     * @param \Mongoeat\Bundle\VoteBundle\Entity\Vote $votes
     * @return Decision
     */
    public function addVote(\Mongoeat\Bundle\VoteBundle\Entity\Vote $votes)
    {
        $this->votes[] = $votes;
    
        return $this;
    }

    /**
     * Remove votes
     *
     * @param \Mongoeat\Bundle\VoteBundle\Entity\Vote $votes
     */
    public function removeVote(\Mongoeat\Bundle\VoteBundle\Entity\Vote $votes)
    {
        $this->votes->removeElement($votes);
    }

    /**
     * Get votes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVotes()
    {
        return $this->votes;
    }
}