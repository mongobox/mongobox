<?php

namespace Mongobox\Bundle\JukeboxBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Mongobox\Bundle\JukeboxBundle\Entity\VideoCurrent
 *
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\JukeboxBundle\Entity\Repository\VideoCurrentRepository")
 * @ORM\Table(name="video_current")
 */
class VideoCurrent
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Videos", inversedBy="videoCurrent")
     * @ORM\JoinColumn(name="id_video", referencedColumnName="id")
     */
    protected $id_video;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\Mongobox\Bundle\GroupBundle\Entity\Group", inversedBy="videos_current")
     * @ORM\JoinColumn(name="id_group", referencedColumnName="id")
     */
    protected $group;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    public function setId($id_video)
    {
        $this->id_video = $id_video;

        return $this;
    }

    public function getId()
    {
        return $this->id_video;
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

	/**
     * Set id_video
     *
     * @param \Mongobox\Bundle\JukeboxBundle\Entity\Videos $idVideo
     * @return VideoCurrent
     */
    public function setIdVideo(\Mongobox\Bundle\JukeboxBundle\Entity\Videos $idVideo)
    {
        $this->id_video = $idVideo;
    
        return $this;
    }

    /**
     * Get id_video
     *
     * @return \Mongobox\Bundle\JukeboxBundle\Entity\Videos 
     */
    public function getIdVideo()
    {
        return $this->id_video;
    }
}