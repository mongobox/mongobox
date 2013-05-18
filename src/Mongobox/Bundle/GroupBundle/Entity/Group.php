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
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $city;
    
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

    /**
     * @ORM\OneToMany(targetEntity="Mongoeat\Bundle\VoteBundle\Entity\Decision", mappedBy="group", cascade={"persist"})
     */
    private $decisions;

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

    /**
     * Remove users
     *
     * @param \Mongobox\Bundle\UsersBundle\Entity\User $users
     */
    public function removeUser(\Mongobox\Bundle\UsersBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Add tumblrs
     *
     * @param \Mongobox\Bundle\TumblrBundle\Entity\Tumblr $tumblrs
     * @return Group
     */
    public function addTumblr(\Mongobox\Bundle\TumblrBundle\Entity\Tumblr $tumblrs)
    {
        $this->tumblrs[] = $tumblrs;
    
        return $this;
    }

    /**
     * Remove tumblrs
     *
     * @param \Mongobox\Bundle\TumblrBundle\Entity\Tumblr $tumblrs
     */
    public function removeTumblr(\Mongobox\Bundle\TumblrBundle\Entity\Tumblr $tumblrs)
    {
        $this->tumblrs->removeElement($tumblrs);
    }

    /**
     * Add playlists
     *
     * @param \Mongobox\Bundle\JukeboxBundle\Entity\Playlist $playlists
     * @return Group
     */
    public function addPlaylist(\Mongobox\Bundle\JukeboxBundle\Entity\Playlist $playlists)
    {
        $this->playlists[] = $playlists;
    
        return $this;
    }

    /**
     * Remove playlists
     *
     * @param \Mongobox\Bundle\JukeboxBundle\Entity\Playlist $playlists
     */
    public function removePlaylist(\Mongobox\Bundle\JukeboxBundle\Entity\Playlist $playlists)
    {
        $this->playlists->removeElement($playlists);
    }

    /**
     * Get playlists
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPlaylists()
    {
        return $this->playlists;
    }

    /**
     * Add videos_group
     *
     * @param \Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup $videosGroup
     * @return Group
     */
    public function addVideosGroup(\Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup $videosGroup)
    {
        $this->videos_group[] = $videosGroup;
    
        return $this;
    }

    /**
     * Remove videos_group
     *
     * @param \Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup $videosGroup
     */
    public function removeVideosGroup(\Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup $videosGroup)
    {
        $this->videos_group->removeElement($videosGroup);
    }

    /**
     * Get videos_group
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVideosGroup()
    {
        return $this->videos_group;
    }

    /**
     * Add group_live_tag
     *
     * @param \Mongobox\Bundle\GroupBundle\Entity\GroupLiveTag $groupLiveTag
     * @return Group
     */
    public function addGroupLiveTag(\Mongobox\Bundle\GroupBundle\Entity\GroupLiveTag $groupLiveTag)
    {
        $this->group_live_tag[] = $groupLiveTag;
    
        return $this;
    }

    /**
     * Remove group_live_tag
     *
     * @param \Mongobox\Bundle\GroupBundle\Entity\GroupLiveTag $groupLiveTag
     */
    public function removeGroupLiveTag(\Mongobox\Bundle\GroupBundle\Entity\GroupLiveTag $groupLiveTag)
    {
        $this->group_live_tag->removeElement($groupLiveTag);
    }

    /**
     * Get group_live_tag
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGroupLiveTag()
    {
        return $this->group_live_tag;
    }

    /**
     * Add users_invitations
     *
     * @param \Mongobox\Bundle\UsersBundle\Entity\User $usersInvitations
     * @return Group
     */
    public function addUsersInvitation(\Mongobox\Bundle\UsersBundle\Entity\User $usersInvitations)
    {
        $this->users_invitations[] = $usersInvitations;
    
        return $this;
    }

    /**
     * Remove users_invitations
     *
     * @param \Mongobox\Bundle\UsersBundle\Entity\User $usersInvitations
     */
    public function removeUsersInvitation(\Mongobox\Bundle\UsersBundle\Entity\User $usersInvitations)
    {
        $this->users_invitations->removeElement($usersInvitations);
    }


    /**
     * Add decisions
     *
     * @param \Mongoeat\Bundle\VoteBundle\Entity\Decision $decisions
     * @return Group
     */
    public function addDecision(\Mongoeat\Bundle\VoteBundle\Entity\Decision $decisions)
    {
        $this->decisions[] = $decisions;
    
        return $this;
    }

    /**
     * Remove decisions
     *
     * @param \Mongoeat\Bundle\VoteBundle\Entity\Decision $decisions
     */
    public function removeDecision(\Mongoeat\Bundle\VoteBundle\Entity\Decision $decisions)
    {
        $this->decisions->removeElement($decisions);
    }

    /**
     * Get decisions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDecisions()
    {
        return $this->decisions;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Group
     */
    public function setCity($city)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }
}