<?php

namespace Mongobox\Bundle\UsersBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mongobox\Bundle\UsersBundle\Entity\UserFavoris
 *
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\UsersBundle\Entity\Repository\UserRepository")
 * @ORM\Table(name="users_favoris")
 */
class UserFavoris
{
	/**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
	protected $id;

	/**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="favoris")
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id")
     */
	protected $user;

	/**
     * @ORM\ManyToOne(targetEntity="Mongobox\Bundle\JukeboxBundle\Entity\Videos", inversedBy="favoris")
     * @ORM\JoinColumn(name="id_video", referencedColumnName="id")
     */
	protected $video;

	/**
	 * @ORM\ManyToOne(targetEntity="ListeFavoris", inversedBy="favoris")
	 * @ORM\JoinColumn(name="id_liste", referencedColumnName="id")
	 */
	protected $liste;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $date_favoris;


	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	public function getUser() {
		return $this->user;
	}

	public function setUser($user) {
		$this->user = $user;
		return $this;
	}

	public function getVideo() {
		return $this->video;
	}

	public function setVideo($video) {
		$this->video = $video;
		return $this;
	}

	public function getListe() {
		return $this->liste;
	}

	public function setListe($liste) {
		$this->liste = $liste;
		return $this;
	}

	public function getDateFavoris() {
		return $this->date_favoris;
	}

	public function setDateFavoris($date_favoris) {
		$this->date_favoris = $date_favoris;
		return $this;
	}
}

?>
