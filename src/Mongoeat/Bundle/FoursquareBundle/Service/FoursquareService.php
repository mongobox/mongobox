<?php

namespace Mongoeat\Bundle\FoursquareBundle\Service;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Mongoeat\Bundle\FoursquareBundle\Exception\FoursquareException;
use Mongoeat\Bundle\RestaurantBundle\Entity;

class FoursquareService
{
    protected $client;
    protected $authentification;

    public function __construct (array $authentification)
    {
        $this->authentification = $authentification;

    }

    public function getClient()
    {
        return $this->client;
    }

    public function getRestaurant($city)
    {
        $this->client = new Client($this->authentification['url_api']);
        try {
            $data = $this->client->get('venues/search?near='.$city.'&categoryId=4d4b7105d754a06374d81259&client_id='.$this->authentification['id'].'&client_secret='.$this->authentification['secret'])->send()->json();
        } catch (ClientErrorResponseException $e1) {
            throw new FoursquareException();
        }
        $liste = array();

        if ($data['meta']['code']==200) {
            foreach ($data['response']['groups'][0]['items'] as $rest) {
                $restaurant = new Entity\Restaurant();
                $restaurant->setName($rest['name']);
                $restaurant->setPhone(isset($rest['contact']['phone']) ? $rest['contact']['phone'] : null);
                $restaurant->setAdresse(isset($rest['location']['address']) ? $rest['location']['address'] : null);
                $restaurant->setCode(isset($rest['location']['postalCode']) ? $rest['location']['postalCode'] : null);
                $restaurant->setCode(isset($rest['location']['postalCode']) ? $rest['location']['postalCode'] : null);
                $restaurant->setLat(isset($rest['location']['lat']) ? $rest['location']['lat'] : null);
                $restaurant->setLng(isset($rest['location']['lng']) ? $rest['location']['lng'] : null);
                $liste[] = $restaurant;
            }
        }

        return $liste;
    }
}
