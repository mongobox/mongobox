<?php

namespace Mongoeat\Bundle\VoteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vote
 *
 * @ORM\Table(name="mongoeat_vote")
 * @ORM\Entity(repositoryClass="Mongoeat\Bundle\VoteBundle\Entity\VoteRepository")
 */
class Vote
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
     * @ORM\ManyToOne(targetEntity="\Mongoeat\Bundle\VoteBundle\Entity\Decision", inversedBy="votes")
     * @ORM\JoinColumn(name="decision_id", referencedColumnName="id")
     */
    private $decision;

    /**
     * @ORM\ManyToOne(targetEntity="\Mongobox\Bundle\UsersBundle\Entity\User", inversedBy="votes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="\Mongoeat\Bundle\RestaurantBundle\Entity\Restaurant", inversedBy="votes")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    private $restaurant;


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
     * Set decision
     *
     * @param \Mongoeat\Bundle\VoteBundle\Entity\Decision $decision
     * @return Vote
     */
    public function setDecision(\Mongoeat\Bundle\VoteBundle\Entity\Decision $decision = null)
    {
        $this->decision = $decision;
    
        return $this;
    }

    /**
     * Get decision
     *
     * @return \Mongoeat\Bundle\VoteBundle\Entity\Decision 
     */
    public function getDecision()
    {
        return $this->decision;
    }

    /**
     * Set user
     *
     * @param \Mongobox\Bundle\UsersBundle\Entity\User $user
     * @return Vote
     */
    public function setUser(\Mongobox\Bundle\UsersBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Mongobox\Bundle\UsersBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set restaurant
     *
     * @param \Mongoeat\Bundle\RestaurantBundle\Entity\Restaurant $restaurant
     * @return Vote
     */
    public function setRestaurant(\Mongoeat\Bundle\RestaurantBundle\Entity\Restaurant $restaurant = null)
    {
        $this->restaurant = $restaurant;
    
        return $this;
    }

    /**
     * Get restaurant
     *
     * @return \Mongoeat\Bundle\RestaurantBundle\Entity\Restaurant 
     */
    public function getRestaurant()
    {
        return $this->restaurant;
    }
}