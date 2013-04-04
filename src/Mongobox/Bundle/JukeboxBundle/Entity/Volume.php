<?php

namespace Mongobox\Bundle\JukeboxBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mongobox\Bundle\JukeboxBundle\Entity\Volume
 *
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\JukeboxBundle\Entity\Repository\VolumeRepository")
 * @ORM\Table(name="volume")
 */
class Volume
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Playlist", inversedBy="volume")
     * @ORM\JoinColumn(name="playlist_id", referencedColumnName="id")
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
    protected $direction;

    public function setPlaylist($playlist)
    {
        $this->playlist = $playlist;

        return $this;
    }

    public function getPlaylist()
    {
        return $this->playlist;
    }

    public function setDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }

    public function getDirection()
    {
        return $this->direction;
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
}
