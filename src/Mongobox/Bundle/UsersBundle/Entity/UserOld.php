<?php

namespace Mongobox\Bundle\UsersBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserOld
 *
 * @ORM\Table(name="user_old")
 * @ORM\Entity
 */
class UserOld
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
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\Email(
     *     message = "'{{ value }}' n'est pas un email valide.",
     *     checkMX = true
     * )
     */
    private $email;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer",name="last_id")
     */
    private $lastId;

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
     * Set email
     *
     * @param  string  $email
     * @return UserOld
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set lastId
     *
     * @param  integer $lastId
     * @return UserOld
     */
    public function setLastId($lastId)
    {
        $this->lastId = $lastId;

        return $this;
    }

    /**
     * Get lastId
     *
     * @return integer
     */
    public function getLastId()
    {
        return $this->lastId;
    }
}
