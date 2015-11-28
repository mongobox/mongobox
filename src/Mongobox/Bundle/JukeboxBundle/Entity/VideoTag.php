<?php

namespace Mongobox\Bundle\JukeboxBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Mongobox\Bundle\JukeboxBundle\Entity\VideoTag
 *
 * @ORM\Table(name="video_tags")
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\JukeboxBundle\Entity\Repository\VideoTagRepository")
 */
class VideoTag
{
    const VIDEO_TAG_REPLACE = 'a_remplacer';

	/**
	 * @var integer $id
	 *
	 * @ORM\Column(name="id_tag", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToMany(targetEntity="Videos", inversedBy="tags")
	 * @ORM\JoinTable(name="video_videos_tags",
	 * 		joinColumns={@ORM\JoinColumn(name="id_tag", referencedColumnName="id_tag")},
	 * 		inverseJoinColumns={@ORM\JoinColumn(name="id_video", referencedColumnName="id")}
	 * )
	 */
	protected $videos;


	/**
	 * @var string $system_name
	 *
	 * @ORM\Column(name="system_name", type="string", length=255)
	 */
	private $system_name;

	/**
	 * @var string $title
	 *
	 * @ORM\Column(name="name", type="string", length=255)
	 */
	private $name;

    /**
     * @ORM\OneToMany(targetEntity="\Mongobox\Bundle\GroupBundle\Entity\GroupLiveTag", mappedBy="video_tag", cascade={"persist"})
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
	protected $group_live_tag;


	public function __construct()
	{
		$this->videos = new ArrayCollection();
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set title
	 *
	 * @param string $title
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set system_name
	 *
	 * @param string $systemName
	 */
	public function setSystemName($systemName)
	{
		$this->system_name = $this::build_SystemName($systemName);
		return $this;
	}

	/**
	 * Get system_name
	 *
	 * @return string
	 */
	public function getSystemName()
	{
		return $this->system_name;
	}

	public function getVideos()
	{
		return $this->videos;
	}

	public function setVideos($videos)
	{
		$this->videos = $videos;
		return $this;
	}

	public function addVideo($video)
	{
		$this->videos[] = $video;
	}

	/**
	 * http://www.ficgs.com/How-to-remove-accents-in-PHP-f3057.html
	 */
	static function build_SystemName($string)
	{
		/**
		 * http://www.ficgs.com/How-to-remove-accents-in-PHP-f3057.html
		 */
		$string = str_replace( array('à','á','â','ã','ä', 'ç', 'è','é','ê','ë', 'ì','í','î','ï', 'ñ', 'ò','ó','ô','õ','ö', 'ù','ú','û','ü', 'ý','ÿ', 'À','Á','Â','Ã','Ä', 'Ç', 'È','É','Ê','Ë', 'Ì','Í','Î','Ï', 'Ñ', 'Ò','Ó','Ô','Õ','Ö', 'Ù','Ú','Û','Ü', 'Ý'), array('a','a','a','a','a', 'c', 'e','e','e','e', 'i','i','i','i', 'n', 'o','o','o','o','o', 'u','u','u','u', 'y','y', 'A','A','A','A','A', 'C', 'E','E','E','E', 'I','I','I','I', 'N', 'O','O','O','O','O', 'U','U','U','U', 'Y'), $string);
		$string = str_replace(array(' ','-'), array('_','_'),$string);

		return strtolower($string);
	}

    public function __toString()
    {
        return $this->name ? : 'New Tag';
    }

}