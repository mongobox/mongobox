<?php
namespace Mongobox\Bundle\JukeboxBundle\Api;

use Symfony\Component\DependencyInjection\ContainerInterface;

// Google API
use Google_Client;
use Google_Service_YouTube;

class Youtube
{
    const YOUTUBE_STATUS_PUBLIC = 'public';
    const YOUTUBE_STATUS_UNLISTED = 'unlisted';

    private $container;

    private $client;
    private $youtubeApi;
    private $googleAppName;
    private $googleDeveloperKey;

    /**
     * Constructor
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->googleAppName = $this->container->getParameter('google_app_name');
        $this->googleDeveloperKey = $this->container->getParameter('google_developer_key');

        $this->initClient();
    }

    private function initClient(){

        $this->client = new Google_Client();
        $this->client->setApplicationName($this->googleAppName);
        $this->client->setDeveloperKey($this->googleDeveloperKey);

        $this->youtubeApi = new Google_Service_YouTube($this->client);
    }

    /**
     *
     */
    public function getYoutubeApi(){
        return $this->youtubeApi;
    }


}