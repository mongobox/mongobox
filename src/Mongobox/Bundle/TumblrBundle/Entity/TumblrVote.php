<?php

namespace Mongobox\Bundle\TumblrBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mongobox\Bundle\TumblrBundle\Entity\TumblrVote
 *
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\TumblrBundle\Entity\Repository\TumblrVoteRepository")
 * @ORM\Table(name="tumblr_vote")
 */
class TumblrVote
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Mongobox\Bundle\UsersBundle\Entity\User", inversedBy="tumblr_vote")
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Tumblr", inversedBy="tumblr_vote")
     * @ORM\JoinColumn(name="id_tumblr", referencedColumnName="id_tumblr")
     */
    protected $tumblr;

    /**
     * @ORM\Column(type="float")
     */
    protected $note;

    public function setTumblr($tumblr)
    {
        $this->tumblr = $tumblr;

        return $this;
    }

    public function getTumblr()
    {
        return $this->tumblr;
    }

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    public function getNote()
    {
        return $this->note;
    }
}
