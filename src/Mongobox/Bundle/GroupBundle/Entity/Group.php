<?php

namespace Mongobox\Bundle\GroupBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Mongobox\Bundle\GroupBundle\Entity\Group
 *
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\GroupBundle\Entity\Repository\GroupRepository")
 * @ORM\Table(name="groups")
 */
class Group
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $title;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $private;

    /**
     * @ORM\ManyToMany(targetEntity="\Mongobox\Bundle\UsersBundle\Entity\User", inversedBy="groups")
     * @ORM\JoinTable(name="users_groups",
     * 		joinColumns={@ORM\JoinColumn(name="id_group", referencedColumnName="id")},
     * 		inverseJoinColumns={@ORM\JoinColumn(name="id_user", referencedColumnName="id")}
     * )
     */
    protected $users;

    /**
     * @ORM\ManyToMany(targetEntity="\Mongobox\Bundle\TumblrBundle\Entity\Tumblr", inversedBy="groups")
     * @ORM\JoinTable(name="tumblrs_groups",
     * 		joinColumns={@ORM\JoinColumn(name="id_group", referencedColumnName="id")},
     * 		inverseJoinColumns={@ORM\JoinColumn(name="id_tumblr", referencedColumnName="id_tumblr")}
     * )
     */
    protected $tumblrs;

    /**
     * @ORM\OneToMany(targetEntity="\Mongobox\Bundle\JukeboxBundle\Entity\Playlist", mappedBy="group", cascade={"persist"})
     * @ORM\JoinColumn(name="id_group", referencedColumnName="id")
     */
	protected $playlists;

    /**
     * @ORM\OneToMany(targetEntity="\Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup", mappedBy="group", cascade={"persist"})
     * @ORM\JoinColumn(name="id_group", referencedColumnName="id")
     */
	protected $videos_group;

    /**
     * @ORM\OneToMany(targetEntity="\Mongobox\Bundle\GroupBundle\Entity\GroupLiveTag", mappedBy="group", cascade={"persist"})
     * @ORM\JoinColumn(name="id_group", referencedColumnName="id")
     */
	protected $group_live_tag;

	/**
     * @ORM\ManyToMany(targetEntity="\Mongobox\Bundle\UsersBundle\Entity\User", inversedBy="groups_invitations")
     * @ORM\JoinTable(name="users_invitations",
     * 		joinColumns={@ORM\JoinColumn(name="id_group", referencedColumnName="id")},
     * 		inverseJoinColumns={@ORM\JoinColumn(name="id_user", referencedColumnName="id")}
     * )
     */
    protected $users_invitations;

	public function __construct()
    {
		//valeurs par défaut
		$this->private = true;
        $this->users = new ArrayCollection();
        $this->users_invitations = new ArrayCollection();
		$this->tumblrs = new ArrayCollection();
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \Mongobox\Bundle\GroupBundle\Entity\Group
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of title.
     *
     * @param string $title
     * @return \Mongobox\Bundle\GroupsBundle\Entity\Group
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the value of private.
     *
     * @param string $private
     * @return \Mongobox\Bundle\GroupsBundle\Entity\Group
     */
    public function setPrivate($private)
    {
        $this->private = $private;

        return $this;
    }

    /**
     * Get the value of private.
     *
     * @return string
     */
    public function getPrivate()
    {
        return $this->private;
    }

    public function addUser($user)
    {
    	$this->users[] = $user;
    	return $this;
    }
    
    public function getUsers()
    {
    	return $this->users;
    }
    
    public function setUsers($users)
    {
    	$this->users = $users;
    	return $this;
    }
    
    public function getTumblrs()
    {
    	return $this->tumblrs;
    }
    
    public function setTumblrs($tumblrs)
    {
    	$this->tumblrs = $tumblrs;
    	return $this;
    }

    public function deleteTumblr($tumblr)
    {
        $this->tumblrs->removeElement($tumblr);
    }

    public function getUsersInvitations()
    {
    	return $this->users_invitations;
    }
    
    public function setUsersInvitations($users_invitations)
    {
    	$this->users_invitations = $users_invitations;
    	return $this;
    }
}