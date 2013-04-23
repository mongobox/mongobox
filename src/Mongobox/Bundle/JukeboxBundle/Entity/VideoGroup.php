<?php

namespace Mongobox\Bundle\JukeboxBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup
 *
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\JukeboxBundle\Entity\Repository\VideoGroupRepository")
 * @ORM\Table(name="videos_groups")
 */
class VideoGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Videos", inversedBy="video_groups")
     * @ORM\JoinColumn(name="id_video", referencedColumnName="id")
     */
    protected $video;

    /**
     * @ORM\ManyToOne(targetEntity="\Mongobox\Bundle\GroupBundle\Entity\Group", inversedBy="videos_group")
     * @ORM\JoinColumn(name="id_group", referencedColumnName="id")
     */
    protected $group;

    /**
     * @ORM\Column(type="datetime", name="last_broadcast", nullable=true)
     */
    protected $lastBroadcast;

    /**
     * @ORM\Column(type="integer")
     */
    protected $diffusion;

    /**
     * @ORM\Column(type="integer", options={"default":50}))
     */
    protected $volume;

    /**
     * @ORM\Column(type="integer")
     */
    protected $votes = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Mongobox\Bundle\UsersBundle\Entity\User", inversedBy="videos_group")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="Playlist", mappedBy="video_group", cascade={"persist"})
     * @ORM\JoinColumn(name="id_video_group", referencedColumnName="id_video_group")
     */
    protected $playlist;

    /**
     * Constructor
     */
    public function __construct() {}

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of video.
     *
     * @param  string                                           $video
     * @return \Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup
     */
    public function setVideo($video)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * Get the value of video.
     *
     * @return string
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Set the value of group.
     *
     * @param  string                                           $group
     * @return \Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get the value of group.
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set the value of lastBroadcast.
     *
     * @param  string                                           $lastBroadcast
     * @return \Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup
     */
    public function setLastBroadcast($lastBroadcast)
    {
        $this->lastBroadcast = $lastBroadcast;

        return $this;
    }

    /**
     * Get the value of lastBroadcast.
     *
     * @return string
     */
    public function getLastBroadcast()
    {
        return $this->lastBroadcast;
    }

    /**
     * Set the value of diffusion.
     *
     * @param  string                                           $diffusion
     * @return \Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup
     */
    public function setDiffusion($diffusion)
    {
        $this->diffusion = $diffusion;

        return $this;
    }

    /**
     * Get the value of diffusion.
     *
     * @return string
     */
    public function getDiffusion()
    {
        return $this->diffusion;
    }

    public function getVolume()
    {
        return $this->volume;
    }

    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
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

    /**
     * Set user
     *
     * @param  \Mongobox\Bundle\UsersBundle\Entity\User $user
     * @return VideoGroup
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
