<?php

namespace Mongobox\Bundle\JukeboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Putsch
 *
 * @ORM\Table(name="putsches_attempts")
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\JukeboxBundle\Entity\Repository\PutschRepository")
 */
class Putsch
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
     * @var \Mongobox\Bundle\GroupBundle\Entity\Group
     *
     * @ORM\ManyToOne(targetEntity="Mongobox\Bundle\GroupBundle\Entity\Group")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    private $group;

    /**
     * @var \Mongobox\Bundle\UsersBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Mongobox\Bundle\UsersBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="response", type="smallint", nullable=true)
     */
    private $response;


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
     * Set group
     *
     * @param \Mongobox\Bundle\GroupBundle\Entity\Group $group
     * @return Putsch
     */
    public function setGroup(\Mongobox\Bundle\GroupBundle\Entity\Group $group)
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
     * Set user
     *
     * @param \Mongobox\Bundle\UsersBundle\Entity\User $user
     * @return Putsch
     */
    public function setUser(\Mongobox\Bundle\UsersBundle\Entity\User $user)
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
     * Set date
     *
     * @param \DateTime $date
     * @return Putsch
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
     * Set response
     *
     * @param integer $response
     * @return Putsch
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get response
     *
     * @return integer
     */
    public function getResponse()
    {
        return $this->response;
    }
}
