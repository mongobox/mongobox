<?php

namespace Mongobox\Bundle\UsersBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Mongobox\Bundle\UsersBundle\Entity\User
 *
 * @ORM\Entity(repositoryClass="Mongobox\Bundle\UsersBundle\Entity\Repository\UserRepository")
 * @ORM\Table(name="users")
 * @UniqueEntity(fields="login", message="Login already in use.")
 */
class User implements AdvancedUserInterface
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
    protected $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $login;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $salt;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\File(
     *     maxSize = "2M",
     *     mimeTypes = {"image/jpeg", "image/gif", "image/png"},
     *     mimeTypesMessage = "Le fichier choisi ne correspond pas à un fichier valide",
     *     notFoundMessage = "Le fichier n'a pas été trouvé sur le disque",
     *     uploadErrorMessage = "Erreur dans l'upload du fichier"
     * )
     */
    protected $avatar;

    /**
     * @ORM\Column(type="integer", length=1)
     */
    protected $actif;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date_create;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date_update;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $last_connect;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $nsfw_mode;

    /**
     * @ORM\OneToMany(targetEntity="Mongobox\Bundle\TumblrBundle\Entity\TumblrVote", mappedBy="user")
     */
	protected $tumblr_vote;

	/**
	 * @ORM\OneToMany(targetEntity="Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup", mappedBy="user")
	 **/
	protected $videos_group;

    /**
     * @ORM\ManyToMany(targetEntity="Mongobox\Bundle\GroupBundle\Entity\Group", mappedBy="users", cascade={"persist"})
     */
    protected $groups;

    /**
     * @ORM\ManyToMany(targetEntity="Mongobox\Bundle\GroupBundle\Entity\Group", mappedBy="users_invitations", cascade={"persist"})
     */
    protected $groups_invitations;

	/**
	 * @ORM\OneToMany(targetEntity="UserFavoris", mappedBy="user")
	 */
	protected $favoris;

	/**
	 * @ORM\OneToMany(targetEntity="ListeFavoris", mappedBy="user")
	 */
	protected $listes_favoris;

	public function __construct()
    {
		//valeurs par défaut
    	$this->date_create = new \DateTime();
        $this->actif = 1;
        $this->nsfw_mode = 0;
		$this->groups = new ArrayCollection();
		$this->groups_invitations = new ArrayCollection();
		$this->favoris = new ArrayCollection();
		$this->listes_favoris = new ArrayCollection();
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \Mongobox\Bundle\UsersBundle\Entity\User
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
     * Set the value of email.
     *
     * @param string $email
     * @return \Mongobox\Bundle\UsersBundle\Entity\User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of login.
     *
     * @param string $login
     * @return \Mongobox\Bundle\UsersBundle\Entity\User
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get the value of login.
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set the value of password.
     *
     * @param string $password
     * @return \Mongobox\Bundle\UsersBundle\Entity\User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of salt.
     *
     * @param string $salt
     * @return \Mongobox\Bundle\UsersBundle\Entity\User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get the value of salt.
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set the value of lastname.
     *
     * @param string $lastname
     * @return \Mongobox\Bundle\UsersBundle\Entity\User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get the value of lastname.
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set the value of firstname.
     *
     * @param string $firstname
     * @return \Mongobox\Bundle\UsersBundle\Entity\User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get the value of firstname.
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set the value of avatar.
     *
     * @param string $avatar
     * @return \Mongobox\Bundle\UsersBundle\Entity\User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get the value of avatar.
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set the value of actif.
     *
     * @param integer $actif
     * @return \Mongobox\Bundle\UsersBundle\Entity\User
     */
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * Get the value of actif.
     *
     * @return boolean
     */
    public function getActif()
    {
        return $this->actif;
    }

    /**
     * Set the value of date_create.
     *
     * @param integer $date_create
     * @return \Mongobox\Bundle\UsersBundle\Entity\User
     */
    public function setDateCreate($date_create)
    {
        $this->date_create = $date_create;

        return $this;
    }

    /**
     * Get the value of date_create.
     *
     * @return integer
     */
    public function getDateCreate()
    {
        return $this->date_create;
    }

    /**
     * Set the value of date_update.
     *
     * @param integer $date_update
     * @return \Mongobox\Bundle\UsersBundle\Entity\User
     */
    public function setDateUpdate($date_update)
    {
        $this->date_update = $date_update;

        return $this;
    }

    /**
     * Get the value of date_update.
     *
     * @return integer
     */
    public function getDateUpdate()
    {
        return $this->date_update;
    }

    /**
     * Set the value of last_connect.
     *
     * @param integer $last_connect
     * @return \Mongobox\Bundle\UsersBundle\Entity\User
     */
    public function setLastConnect($last_connect)
    {
        $this->last_connect = $last_connect;

        return $this;
    }

    /**
     * Get the value of last_connect.
     *
     * @return integer
     */
    public function getLastConnect()
    {
        return $this->last_connect;
    }

    /**
     * Set the value of nsfw_mode
     *
     * @param integer $last_connect
     * @return \Mongobox\Bundle\UsersBundle\Entity\User
     *
     */
    public function setNsfwMode($nsfw)
    {
        $this->nsfw_mode = $nsfw;
        return $this;
    }

    /**
     * Get the value of nsfw_mode
     *
     * @return boolean
     */
    public function getNsfwMode()
    {
        return $this->nsfw_mode;
    }

    /**
     * Get tumblr_vote
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTumblrVote()
    {
        return $this->tumblr_vote;
    }

    /**
     * Add videos
     *
     * @param \Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup $videos_group
     * @return User
     */
    public function addVideosGroup(\Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup $videos_group)
    {
        $this->videos_group[] = $videos_group;

        return $this;
    }

    /**
     * Remove videos
     *
     * @param \Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup $videos_group
     */
    public function removeVideosGroup(\Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup $videos_group)
    {
        $this->videos_group->removeElement($videos_group);
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

    public function addGroup($group)
    {
    	$this->groups[] = $group;
    	return $this;
    }

    public function getGroups()
    {
    	return $this->groups;
    }

    public function setGroups($groups)
    {
    	$this->groups = $groups;
    	return $this;
    }

    public function getGroupsInvitations()
    {
    	return $this->groups_invitations;
    }

    public function setGroupsInvitations($groups_invitations)
    {
    	$this->groups_invitations = $groups_invitations;
    	return $this;
    }

	public function getGroupDefault()
	{
        $groups = $this->getGroups();
		return $groups[0]->getId();
	}

	/**
	 * Fonction permettant de faire la correspondance entre les rôles en BDD et ceux de Symfony
	 * @param integer $id_role
	 */
	public function getRoleCorrespondance($id_role)
    {
        switch($id_role)
        {
            case 1 :
                return 'ROLE_SUPER_ADMIN';
            break;
            case 2 :
                return 'ROLE_ADMIN';
            break;
            case 3 :
                return 'ROLE_USER';
            break;
        }
    }

	/**
	 * Récupère tous les rôles symfony de l'utilisateur en fonction de ses communautés
	 */
    public function getRoles()
    {
    	$roles = array('ROLE_USER');
    	return $roles;
    }


    public function getGroupsIds()
	{
		$groups_ids = array();
		foreach($this->getGroups() as $group)
		{
			$groups_ids[] = $group->getId();
		}
		return $groups_ids;
	}

	public function isMemberFrom($id_group)
	{
		foreach($this->getGroups() as $group_user)
		{
			if($group_user->getId() == $id_group) return true;
		}
		return false;
	}

	/**
	 * Encode le mot de passe
	 * @param PasswordEncoderInterface $encoder
	 */
    public function encodePassword(PasswordEncoderInterface $encoder)
    {
        if($this->password)
	{
            $this->salt = sha1(uniqid().time().rand(0,999999));
            $this->password = $encoder->encodePassword
            (
                $this->password,
                $this->salt
            );
        }
    }

	/**
	 * Renvoi si le compte est non-expiré
	 */
    public function isAccountNonExpired()
    {
    	return true;
    }

	/**
	 * Renvoi si le compte est actif
	 */
    public function isEnabled()
    {
		if($this->actif == 1) return true;
    	else return false;
    }

    public function isCredentialsNonExpired()
    {
    	return true;
    }

    public function isAccountNonLocked()
    {
    	return true;
    }

    public function eraseCredentials()
    {
    	$this->Password = null;
    }

	/**
	 * Retourne l'username
	 */
    public function getUsername()
    {
        return $this->login;
    }

         public function serialize()
         {
                return serialize($this->getUserName());
         }

         public function unserialize($data)
         {
                $this->username = unserialize($data);
         }

	/**
	 * Renvoi le role de l'utilisateur
	 */
	public function getRole()
	{
		return 'User';
	}

	public function getGravatar($s = 50)
	{
		return 'http://www.gravatar.com/avatar/'.md5( strtolower( trim( $this->getEmail() ) ) ).'?s='.$s;
	}

	/**
	 * Retourne le chemin absolut vers l'avatar
	 */
    public function getAbsolutePath()
    {
        return null === $this->avatar ? null : $this->getUploadRootDir().'/'.$this->avatar;
    }

	/**
	 * Retourne le chemin web vers l'avatar
	 */
    public function getAvatarWebPath()
    {
        return $this->getUploadDir().'/'.$this->avatar;
    }

	/**
	 * Retourne le répertoire permetant l'upload
	 */
    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__.'/../../../../../web/'.$this->getUploadDir();
    }

	/**
	 * Retourne le répertoire permettant l'upload des avatars
	 */
    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'avatars';
    }

	/**
	 * Permet l'upload de l'avatar, et la suppression des caches de thumbnail
	 */
    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->avatar)
        {
            return;
        }

        //Nom du fichier
        $file = $this->id.'.'.$this->avatar->guessExtension();
        // move takes the target directory and then the target filename to move to
        $this->avatar->move($this->getUploadRootDir(), $file);
        //Suppression des thumbnail déjà  en cache
        @unlink(__DIR__.'/../../../../../web/imagine/avatar_thumbnail/avatars/'.$file);
        @unlink(__DIR__.'/../../../../../web/imagine/avatar_moyen/avatars/'.$file);
        @unlink(__DIR__.'/../../../../../web/imagine/avatar_mini/avatars/'.$file);

        // set the path property to the filename where you'ved saved the file
        $this->avatar = $file;
    }

	//Génère un lastname utilisable via l'url
	public function getLastnameUrl()
	{
		$lastname = $this->getLastname();
		$translit = array('Á'=>'A','À'=>'A','Â'=>'A','Ä'=>'A','Ã'=>'A','Å'=>'A','Ç'=>'C','É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E','Í'=>'I','Ï'=>'I','Î'=>'I','Ì'=>'I','Ñ'=>'N','Ó'=>'O','Ò'=>'O','Ô'=>'O','Ö'=>'O','Õ'=>'O','Ú'=>'U','Ù'=>'U','Û'=>'U','Ü'=>'U','Ý'=>'Y','á'=>'a','à'=>'a','â'=>'a','ä'=>'a','ã'=>'a','å'=>'a','ç'=>'c','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','í'=>'i','ì'=>'i','î'=>'i','ï'=>'i','ñ'=>'n','ó'=>'o','ò'=>'o','ô'=>'o','ö'=>'o','õ'=>'o','ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','ý'=>'y','ÿ'=>'y','-'=>'','_'=>'',' '=>'');
		$lastname = strtr($lastname, $translit);
		return preg_replace('#[^a-zA-Z0-9\-\._]#', '', $lastname);
		//return $lastname;
	}

    // Fonction pour récupérer le vote d'un utilisateur pour un tumblr donnée
    public function getNoteForTumblr($id_tumblr)
    {
        foreach($this->tumblr_vote as $tumblrVote)
        {
            if($tumblrVote->getTumblr()->getId() === $id_tumblr) return floatval($tumblrVote->getNote());
        }
        return 0;
    }

	public function getFavoris() {
		return $this->favoris;
	}

	public function setFavoris($favoris) {
		$this->favoris = $favoris;
		return $this;
	}

	public function getListesFavoris() {
		return $this->listes_favoris;
	}
}
