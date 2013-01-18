<?php

namespace Emk\Bundle\JukeboxBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Emk\Bundle\JukeboxBundle\Entity\Videos
 *
 * @ORM\Entity(repositoryClass="Emk\Bundle\JukeboxBundle\Entity\Repository\VideosRepository")
 * @ORM\Table(name="videos")
 */
class Videos
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $lien;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @ORM\Column(type="datetime", name="last_broadcast", nullable=true)
     */
    protected $lastBroadcast;

    /**
     * @ORM\Column(type="integer")
     */
    protected $diffusion;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $addressIp;

    /**
     * @ORM\Column(type="integer")
     */
    protected $duration;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $vendredi;

    /**
     * @ORM\Column(type="integer")
     */
    protected $volume = 100;

    /**
     * @ORM\Column(type="integer")
     */
    protected $votes = 0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $thumbnail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $thumbnailHq;

    /**
     * @ORM\OneToMany(targetEntity="VideoCurrent", mappedBy="id_video", cascade={"persist"})
     * @ORM\JoinColumn(name="id_video", referencedColumnName="id_video")
     */
    protected $videoCurrent;

    /**
     * @ORM\OneToMany(targetEntity="Playlist", mappedBy="video", cascade={"persist"})
     * @ORM\JoinColumn(name="id_video", referencedColumnName="id_video")
     */
    protected $playlist;

	/**
     * @ORM\ManyToOne(targetEntity="Emakina\Bundle\LdapBundle\Entity\User", inversedBy="videos")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

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
     * Set the value of lien.
     *
     * @param  string                                  $lien
     * @return \Emk\Bundle\JukeboxBundle\Entity\Videos
     */
    public function setLien($lien)
    {
        $this->lien = $lien;

        return $this;
    }

    /**
     * Get the value of lien.
     *
     * @return string
     */
    public function getLien()
    {
        return $this->lien;
    }

    /**
     * Set the value of date.
     *
     * @param  string                                  $date
     * @return \Emk\Bundle\JukeboxBundle\Entity\Videos
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get the value of date.
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set the value of lastBroadcast.
     *
     * @param  string                                  $lastBroadcast
     * @return \Emk\Bundle\JukeboxBundle\Entity\Videos
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
     * Set the value of done.
     *
     * @param  string                                  $done
     * @return \Emk\Bundle\JukeboxBundle\Entity\Videos
     */
    public function setDone($done)
    {
        $this->done = $done;

        return $this;
    }

    /**
     * Get the value of done.
     *
     * @return string
     */
    public function getDone()
    {
        return $this->done;
    }

    /**
     * Set the value of diffusion.
     *
     * @param  string                                  $diffusion
     * @return \Emk\Bundle\JukeboxBundle\Entity\Videos
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

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getThumbnailHq()
    {
        return $this->thumbnailHq;
    }

    public function setThumbnailHq($thumbnailHq)
    {
        $this->thumbnailHq = $thumbnailHq;

        return $this;
    }

    public function getAddressIp()
    {
        return $this->addressIp;
    }

    public function setAddressIp($addressIp)
    {
        $this->addressIp = $addressIp;

        return $this;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    public function isVendredi()
    {
        return $this->vendredi;
    }
    
    public function getVendredi()
    {
        return $this->vendredi;
    }

    public function setVendredi($vendredi)
    {
        $this->vendredi = $vendredi;

        return $this;
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

    public function getTitleFromYoutube()
    {
        $feed = 'http://gdata.youtube.com/feeds/api/videos/'.$this->getLien();
        $xml = simplexml_load_file($feed);

        return (string) $xml->title;
    }

    public function getDataFromYoutube()
    {
        $feed = 'http://gdata.youtube.com/feeds/api/videos/'.$this->getLien() . '?v=2&alt=jsonc';

        $json = file_get_contents($feed);
        $data = json_decode($json);

        return $data->data;
    }

    public function getYoutubeUrl()
    {
        return 'http://www.youtube.com/watch?v='.$this->getLien();
    }

    /**
     * Split a given URL into its components.
     * Uses parse_url() followed by parse_str() on the query string.
     *
     * @param  string $url The string to decode.
     * @return array  Associative array containing the different components.
     */
    public static function parse_url_detail($url)
    {
        $parts = parse_url($url);

        if (isset($parts['query'])) {
            parse_str(urldecode($parts['query']), $parts['query']);

            return $parts['query']['v'];
        } else return $url;

    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->videoCurrent = new \Doctrine\Common\Collections\ArrayCollection();
        $this->playlist = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add videoCurrent
     *
     * @param \Emk\Bundle\JukeboxBundle\Entity\VideoCurrent $videoCurrent
     * @return Videos
     */
    public function addVideoCurrent(\Emk\Bundle\JukeboxBundle\Entity\VideoCurrent $videoCurrent)
    {
        $this->videoCurrent[] = $videoCurrent;
    
        return $this;
    }

    /**
     * Remove videoCurrent
     *
     * @param \Emk\Bundle\JukeboxBundle\Entity\VideoCurrent $videoCurrent
     */
    public function removeVideoCurrent(\Emk\Bundle\JukeboxBundle\Entity\VideoCurrent $videoCurrent)
    {
        $this->videoCurrent->removeElement($videoCurrent);
    }

    /**
     * Get videoCurrent
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVideoCurrent()
    {
        return $this->videoCurrent;
    }

    /**
     * Add playlist
     *
     * @param \Emk\Bundle\JukeboxBundle\Entity\Playlist $playlist
     * @return Videos
     */
    public function addPlaylist(\Emk\Bundle\JukeboxBundle\Entity\Playlist $playlist)
    {
        $this->playlist[] = $playlist;
    
        return $this;
    }

    /**
     * Remove playlist
     *
     * @param \Emk\Bundle\JukeboxBundle\Entity\Playlist $playlist
     */
    public function removePlaylist(\Emk\Bundle\JukeboxBundle\Entity\Playlist $playlist)
    {
        $this->playlist->removeElement($playlist);
    }

    /**
     * Get playlist
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPlaylist()
    {
        return $this->playlist;
    }

    /**
     * Set user
     *
     * @param \Emakina\Bundle\LdapBundle\Entity\User $user
     * @return Videos
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