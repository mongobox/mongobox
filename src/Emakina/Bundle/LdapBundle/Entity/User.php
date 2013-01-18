<?php

namespace Emakina\Bundle\LdapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @UniqueEntity("trigramme")
 * @ORM\Entity(repositoryClass="Emakina\Bundle\LdapBundle\Entity\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=1024, nullable=false)
     * @Assert\NotBlank()
     */
    protected $dn;

    /**
     * @ORM\Column(type="string", length=3, unique=true, nullable=false)
     * @Assert\NotBlank()
     * @Assert\MinLength(limit=2)
     * @Assert\MaxLength(limit=3)
     */
    protected $trigramme;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Assert\NotBlank()
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Assert\NotBlank()
     */
    protected $lastname;

    /**
	 * Pour retro compatibilité
	 *
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url()
     */
    protected $photo;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Min(limit = "0")
     * @Assert\Max(limit = "1")
     */
    protected $is_enabled;
	
	/**
	 * @ORM\OneToMany(targetEntity="Mongobox\Bundle\JukeboxBundle\Entity\Videos", mappedBy="user")
	 **/
	private $videos;
	
	public function __construct() {
		$this->videos = new ArrayCollection();
	}
	

	public function transform($ldapUser) {
		$this->setDn($ldapUser->get('dn'))
			 ->setEmail($ldapUser->get('mail'))
			 ->setName($ldapUser->get('displayname'))
			 ->setFirstname($ldapUser->get('givenname'))
			 ->setLastname($ldapUser->get('sn'))
			 ->setPhoto($ldapUser->get('photo'))
			 ->setTrigramme($ldapUser->get('trigramme'))
		;
		
        return $this;
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
     * Set dn
     *
     * @param string $dn
     * @return User
     */
    public function setDn($dn)
    {
        $this->dn = $dn;
    
        return $this;
    }

    /**
     * Get dn
     *
     * @return string 
     */
    public function getDn()
    {
        return $this->dn;
    }

    /**
     * Set trigramme
     *
     * @param string $trigramme
     * @return User
     */
    public function setTrigramme($trigramme)
    {
        $this->trigramme = $trigramme;
    
        return $this;
    }

    /**
     * Get trigramme
     *
     * @return string 
     */
    public function getTrigramme()
    {
        return $this->trigramme;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    
        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    
        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set photo
     *
     * @param string $photo
     * @return User
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    
        return $this;
    }

    /**
     * Get photo
     *
     * @return string 
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set is_enabled
     *
     * @param boolean $isEnabled
     * @return User
     */
    public function setIsEnabled($isEnabled)
    {
        $this->is_enabled = $isEnabled;
    
        return $this;
    }

    /**
     * Get is_enabled
     *
     * @return boolean 
     */
    public function getIsEnabled()
    {
        return $this->is_enabled;
    }

    /**
     * Add videos
     *
     * @param \Mongobox\Bundle\JukeboxBundle\Entity\Videos $videos
     * @return User
     */
    public function addVideo(\Mongobox\Bundle\JukeboxBundle\Entity\Videos $videos)
    {
        $this->videos[] = $videos;
    
        return $this;
    }

    /**
     * Remove videos
     *
     * @param \Mongobox\Bundle\JukeboxBundle\Entity\Videos $videos
     */
    public function removeVideo(\Mongobox\Bundle\JukeboxBundle\Entity\Videos $videos)
    {
        $this->videos->removeElement($videos);
    }

    /**
     * Get videos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVideos()
    {
        return $this->videos;
    }
}