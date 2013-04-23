<?php

namespace Mongobox\Bundle\StatisticsBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Mongobox\Bundle\UsersBundle\Entity\User;

/**
 * Activity
 *
 * @ORM\Table(name="users_activities")
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\StatisticsBundle\Entity\Repository\User\ActivityRepository")
 */
class Activity
{
    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Mongobox\Bundle\UsersBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="type_id", type="integer")
     */
    private $typeId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * Set user
     *
     * @param  User     $user
     * @return Activity
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set typeId
     *
     * @param  integer  $typeId
     * @return Activity
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Get typeId
     *
     * @return integer
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set date
     *
     * @param  \DateTime $date
     * @return Activity
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
}
