<?php

namespace Mongobox\Bundle\JukeboxBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * Mongobox\Bundle\JukeboxBundle\Entity\Videos
 *
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\JukeboxBundle\Entity\Repository\VideosRepository")
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
     * @ORM\Column(type="string", length=255)
     */
    protected $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $artist;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $songName;

    /**
     * @ORM\Column(type="integer")
     */
    protected $duration;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $thumbnail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $thumbnailHq;

    /**
     * @ORM\OneToMany(targetEntity="VideoGroup", mappedBy="video", cascade={"persist"})
     * @ORM\JoinColumn(name="id_video", referencedColumnName="id")
     */
	protected $video_groups;

    /**
     * @ORM\ManyToMany(targetEntity="VideoTag", mappedBy="videos", cascade={"persist"})
     */
    protected $tags;

	/**
     * Constructor
     */
    public function __construct()
    {
        $this->playlist = new ArrayCollection();
    }

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
     * @return \Mongobox\Bundle\JukeboxBundle\Entity\Videos
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
     * @return \Mongobox\Bundle\JukeboxBundle\Entity\Videos
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

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getArtist()
    {
        return $this->artist;
    }

    public function setArtist($artist)
    {
        $this->artist = $artist;

        return $this;
    }

    public function getSongName()
    {
        return $this->songName;
    }

    public function setSongName($songName)
    {
        $this->songName = $songName;

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

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    public function getTitleFromYoutube()
    {
        $feed = 'http://gdata.youtube.com/feeds/api/videos/'.$this->getLien();
        $xml = simplexml_load_file($feed);

        return (string) $xml->title;
    }

    static function getDataFromYoutube($YoutubeId)
    {
        $feed = 'http://gdata.youtube.com/feeds/api/videos/'. $YoutubeId . '?v=2&alt=jsonc';

        $json = file_get_contents($feed);
        $data = json_decode($json);

        return $data->data;
    }

    public function getYoutubeUrl()
    {
        return 'http://www.youtube.com/watch?v='.$this->getLien();
    }

	public function guessVideoInfos()
	{
		$infos = array('artist' => '', 'songName' => '');
		$split = explode('-', $this->title);
		if(count($split) > 1)
		{
			$infos['artist'] = trim($split[0]);
			$infos['songName'] = trim($split[1]);
		}
		return $infos;
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
     * Add playlist
     *
     * @param \Mongobox\Bundle\JukeboxBundle\Entity\Playlist $playlist
     * @return Videos
     */
    public function addPlaylist(\Mongobox\Bundle\JukeboxBundle\Entity\Playlist $playlist)
    {
        $this->playlist[] = $playlist;

        return $this;
    }

    /**
     * Remove playlist
     *
     * @param \Mongobox\Bundle\JukeboxBundle\Entity\Playlist $playlist
     */
    public function removePlaylist(\Mongobox\Bundle\JukeboxBundle\Entity\Playlist $playlist)
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
     *
     * @param TumblrTag $tag
     */
    public function addTag($tag) {
        // var_dump( $tag);exit;
        $tag->addVideo($this);
        $this->tags[] = $tag;
        return $this;
    }

    /**
     * Fonction to delete tag
     * @param Discussion $discussion
     */
    public function removeTag($tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * @return the $tags
     */
    public function getTags() {
        return $this->tags;
    }

    /**
     * @return the $tags
     */
    public function setTags(ArrayCollection $tags) {
        $this->tags = $tags;
        return $this;
    }

	public function getName()
	{
		if($this->getSongName() != '') return $this->getArtist().' - '.$this->getSongName ();
		else return $this->getTitle();
	}
}