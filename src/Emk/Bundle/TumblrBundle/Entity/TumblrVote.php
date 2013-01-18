<?php

namespace Emk\Bundle\TumblrBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Emk\Bundle\TumblrBundle\Entity\TumblrVote
 *
 * @ORM\Entity(repositoryClass="Emk\Bundle\TumblrBundle\Entity\Repository\TumblrVoteRepository")
 * @ORM\Table(name="tumblr_vote")
 */
class TumblrVote
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $ip;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Tumblr", inversedBy="tumblr_vote")
     * @ORM\JoinColumn(name="id_tumblr", referencedColumnName="id_tumblr")
     */
    protected $tumblr;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sens;

    public function setTumblr($tumblr)
    {
        $this->tumblr = $tumblr;

        return $this;
    }

    public function getTumblr()
    {
        return $this->tumblr;
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
}
