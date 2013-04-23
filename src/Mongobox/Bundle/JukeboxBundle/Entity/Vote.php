<?php

namespace Mongobox\Bundle\JukeboxBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mongobox\Bundle\JukeboxBundle\Entity\Vote
 *
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\JukeboxBundle\Entity\Repository\VoteRepository")
 * @ORM\Table(name="vote")
 */
class Vote
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Playlist", inversedBy="votes")
     * @ORM\JoinColumn(name="id_playlist", referencedColumnName="id")
     */
    protected $playlist;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Mongobox\Bundle\UsersBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sens;

    public function setPlaylist($playlist)
    {
        $this->playlist = $playlist;

        return $this;
    }

    public function getPlaylist()
    {
        return $this->playlist;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setSens($sens)
    {
        $this->sens = $sens;

        return $this;
    }

    public function getSens()
    {
        return $this->sens;
    }

    /**
     * Set user
     *
     * @param  \Mongobox\Bundle\UsersBundle\Entity\User $user
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
}
