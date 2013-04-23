<?php

namespace Mongobox\Bundle\JukeboxBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mongobox\Bundle\JukeboxBundle\Entity\Playlist
 *
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\JukeboxBundle\Entity\Repository\PlaylistRepository")
 * @ORM\Table(name="playlist")
 */
class Playlist
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="VideoGroup", inversedBy="playlist")
     * @ORM\JoinColumn(name="id_video_group", referencedColumnName="id")
     */
    protected $video_group;

    /**
     * @ORM\ManyToOne(targetEntity="\Mongobox\Bundle\GroupBundle\Entity\Group", inversedBy="playlists")
     * @ORM\JoinColumn(name="id_group", referencedColumnName="id")
     */
    protected $group;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     *
     * @ORM\Column(type="boolean")
     */
    protected $random = 0;

    /**
     *
     * @ORM\Column(type="boolean")
     */
    protected $current = 0;

    /**
     * @ORM\OneToMany(targetEntity="Vote", mappedBy="playlist")
     */
    protected $votes;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setVideoGroup($video_group)
    {
        $this->video_group = $video_group;

        return $this;
    }

    public function getVideoGroup()
    {
        return $this->video_group;
    }

    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setRandom($random)
    {
        $this->random = $random;

        return $this;
    }

    public function getRandom()
    {
        return $this->random;
    }

    public function setCurrent($current)
    {
        $this->current = $current;

        return $this;
    }

    public function getCurrent()
    {
        return $this->current;
    }

    public function setVotes($votes)
    {
        $this->votes = $votes;

        return $this;
    }

    public function getVotes()
    {
        return $this->votes;
    }
}
