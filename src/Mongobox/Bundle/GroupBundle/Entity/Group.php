<?php

namespace Mongobox\Bundle\GroupBundle\Entity;

use FOS\UserBundle\Model\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

use Mongobox\Bundle\UsersBundle\Entity\User;

/**
 * Mongobox\Bundle\GroupBundle\Entity\Group
 *
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\GroupBundle\Entity\Repository\GroupRepository")
 * @ORM\Table(name="groups")
 */
class Group extends BaseGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    protected $roles =array();

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
     * @var integer
     *
     * @ORM\Column(name="live_max_dislikes", type="integer", nullable=true)
     */
    protected $liveMaxDislikes;

    /**
     * @var integer
     *
     * @ORM\Column(name="next_putsch_waiting", type="integer", nullable=true)
     */
    protected $nextPutschWaiting;

    /**
     * @var string
     *
     * @ORM\Column(name="secret_key", type="string", length=255)
     */
    protected $secretKey;

    /**
     * @var \Mongobox\Bundle\UsersBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Mongobox\Bundle\UsersBundle\Entity\User")
     * @ORM\JoinColumn(name="live_current_admin", referencedColumnName="id")
     */
    protected $liveCurrentAdmin;

    /**
     * @ORM\ManyToMany(targetEntity="\Mongobox\Bundle\UsersBundle\Entity\User", mappedBy="groups", cascade={"all"})
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

    /**
     * Constructor
     */
    public function __construct()
    {
       // var_dump(__METHOD__,$name,$roles);exit;
        //parent::__construct($name, $roles);

		$this->private              = true;
        $this->users                = new ArrayCollection();
        $this->users_invitations    = new ArrayCollection();
		$this->tumblrs              = new ArrayCollection();
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

    public function getLiveMaxDislikes()
    {
        return $this->liveMaxDislikes;
    }

    public function setLiveMaxDislikes($liveMaxDislikes)
    {
        $this->liveMaxDislikes = $liveMaxDislikes;
        return $this;
    }

    public function getNextPutschWaiting()
    {
        return $this->nextPutschWaiting;
    }

    public function setNextPutschWaiting($nextPutschWaiting)
    {
        $this->nextPutschWaiting = $nextPutschWaiting;
        return $this;
    }

    public function getLiveCurrentAdmin()
    {
        return $this->liveCurrentAdmin;
    }

    public function setLiveCurrentAdmin($user)
    {
        $this->liveCurrentAdmin = $user;
        return $this;
    }

    public function getSecretKey()
    {
        return $this->secretKey;
    }

    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
        return $this;
    }

    /* -------------------- Manage users ------------------------- */
    public function addUser(User $user)
    {
        if (!$this->users->contains($user)) {
            $user->addGroup($this);
            $this->users[] = $user;
        }

        return $this;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function setUsers(ArrayCollection $users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \Mongobox\Bundle\UsersBundle\Entity\User $users
     */
    public function removeUser(User $user)
    {
        //$user->removeGroup($this);
        $this->users->removeElement($user);
    }

    /* -------------------- Manage tumblrs ------------------------- */
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

    /** ---------- USER INVITATIONS ---------- */

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
     * Add users_invitations
     *
     * @param \Mongobox\Bundle\UsersBundle\Entity\User $usersInvitations
     * @return Group
     */
    public function addUsersInvitation(User $usersInvitations)
    {
        if (!$this->users_invitations->contains($usersInvitations)) {
            $this->users_invitations[] = $usersInvitations;
        }

        return $this;
    }

    /**
     * Remove users_invitations
     *
     * @param \Mongobox\Bundle\UsersBundle\Entity\User $usersInvitations
     */
    public function removeUsersInvitation(User $usersInvitations)
    {
        $this->users_invitations->removeElement($usersInvitations);
    }

    public function hasUsersInvitation(User $usersInvitations){
        return $this->users_invitations->contains($usersInvitations);
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

    public function __toString()
    {
        return $this->name ? : 'New Group';
    }
}
