<?php

namespace Mongoeat\Bundle\RestaurantBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Restaurant
 *
 * @ORM\Table(name="mongoeat_restaurant")
 * @ORM\Entity(repositoryClass="Mongoeat\Bundle\RestaurantBundle\Entity\RestaurantRepository")
 */
class Restaurant
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="text", nullable=true)
     */
    private $adresse;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=6, nullable=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="lat", type="string", length=255, nullable=true)
     */
    private $lat;

    /**
     * @var string
     *
     * @ORM\Column(name="lng", type="string", length=255, nullable=true)
     */
    private $lng;

    /**
     * @ORM\OneToMany(targetEntity="Mongoeat\Bundle\VoteBundle\Entity\Vote", mappedBy="restaurant", cascade={"persist"})
     */
    private $votes;

    public function toArray()
    {
        return array(
            'id'=>$this->id,
            'name'=>$this->name,
            'phone'=>$this->phone,
            'adresse'=>$this->adresse,
            'code'=>$this->code,
            'city'=>$this->city,
            'lat'=>$this->lat,
            'lng'=>$this->lng
        );
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
     * Set name
     *
     * @param  string     $name
     * @return Restaurant
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
     * Set phone
     *
     * @param  string     $phone
     * @return Restaurant
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set adresse
     *
     * @param  string     $adresse
     * @return Restaurant
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse
     *
     * @return string
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set lat
     *
     * @param  string     $lat
     * @return Restaurant
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return string
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param  string     $lng
     * @return Restaurant
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng
     *
     * @return string
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set code
     *
     * @param  string     $code
     * @return Restaurant
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set city
     *
     * @param  string     $city
     * @return Restaurant
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

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->votes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add votes
     *
     * @param  \Mongoeat\Bundle\VoteBundle\Entity\Vote $votes
     * @return Restaurant
     */
    public function addVote(\Mongoeat\Bundle\VoteBundle\Entity\Vote $votes)
    {
        $this->votes[] = $votes;

        return $this;
    }

    /**
     * Remove votes
     *
     * @param \Mongoeat\Bundle\VoteBundle\Entity\Vote $votes
     */
    public function removeVote(\Mongoeat\Bundle\VoteBundle\Entity\Vote $votes)
    {
        $this->votes->removeElement($votes);
    }

    /**
     * Get votes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVotes()
    {
        return $this->votes;
    }

    public function __toString()
    {
        return $this->name;
    }
}
