<?php

namespace Mongobox\Bundle\BookmarkBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mongobox\Bundle\BookmarkBundle\Entity\ListeFavoris
 *
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\BookmarkBundle\Entity\Repository\ListeFavorisRepository")
 * @ORM\Table(name="listes_favoris")
 */
class ListeFavoris
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

	/**
     * @ORM\ManyToOne(targetEntity="Mongobox\Bundle\UsersBundle\Entity\User", inversedBy="listes_favoris")
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date_creation;

    /**
     * @ORM\OneToMany(targetEntity="UserFavoris", mappedBy="liste")
     */
    protected $favoris;


    public function getId()
    {
        return $this->id;
    }

	public function setId($id) {
		$this->id = $id;
		return $this;
	}

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getDate_creation()
    {
        return $this->date_creation;
    }

    public function setDate_creation($date_creation)
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getFavoris()
    {
        return $this->favoris;
    }

    public function setFavoris($fav)
    {
        $this->favoris = $fav;

        return $this;
    }
}
