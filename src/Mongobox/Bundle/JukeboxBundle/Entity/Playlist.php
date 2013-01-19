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
     * @ORM\ManyToOne(targetEntity="Videos", inversedBy="playlist")
     * @ORM\JoinColumn(name="id_video", referencedColumnName="id")
     */
    protected $video;

    /**
     * @ORM\Id
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

	public function setVideo($video)
    {
        $this->video = $video;

        return $this;
    }

    public function getVideo()
    {
        return $this->video;
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
}