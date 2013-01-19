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
     * @ORM\Column(type="string")
     */
    protected $ip;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Videos", inversedBy="playlist")
     * @ORM\JoinColumn(name="id_video", referencedColumnName="id")
     */
    protected $video;

    /**
     * @ORM\ManyToOne(targetEntity="Emakina\Bundle\LdapBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sens;

    public function setVideo($video)
    {
        $this->video = $video;

        return $this;
    }

    public function getVideo()
    {
        return $this->video;
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
     * @param \Emakina\Bundle\LdapBundle\Entity\User $user
     * @return Vote
     */
    public function setUser(\Emakina\Bundle\LdapBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Emakina\Bundle\LdapBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}