<?php

namespace Mongobox\Bundle\JukeboxBundle\Live;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Configurator
{
    private $container;

    /**
     * Constructor
     */
    public function __construct(ContainerInterface $container, EntityManager $entityManager)
    {
        $this->container    = $container;
        $this->em           = $entityManager;
    }

    /**
     * Initialize the jukebox parameters
     *
     * @param boolean $adminMode
     * @return array
     */
    public function initializeJukebox($adminMode = false)
    {
        $request    = $this->container->get('request');
        $session    = $this->container->get('session');

        $currentGroup = $this->em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));
        $currentVideo = $this->em->getRepository('MongoboxJukeboxBundle:Playlist')->findOneBy(array(
            'group' => $currentGroup->getId(), 'current' => 1
        ));

        if (is_object($currentVideo)) {
            $currentPlayed = $currentVideo;
        } else {
            $currentPlayed = $this->get('mongobox_jukebox.live_admin')->initializePlaylist($currentGroup);
        }

        $currentDate	= new \DateTime();
        $startDate		= $currentPlayed->getVideoGroup()->getLastBroadcast();

        $secondsElapsed = $currentDate->getTimestamp() - $startDate->getTimestamp();
        if ($secondsElapsed < $currentPlayed->getVideoGroup()->getVideo()->getDuration()) {
            $playerStart = $secondsElapsed;
        } else {
            $playerStart = 0;
        }

        $playerVars = array(
            'start'             => $playerStart,
            'autoplay'          => 1,
            'volume'            => $currentPlayed->getVideoGroup()->getVolume(),
            'iv_load_policy'    => 3,
            'rel'               => 0
        );

        $playerMode = $adminMode ? 'admin' : 'showOnly';

        if ($playerMode !== 'mobile')  {
            $playerWidth 	= '800px';
            $playerHeight	= '500px';
        } else {
            $playerWidth 	= '390px';
            $playerHeight	= '220px';

            $playerVars['mode'] = 'opaque';
        }

        if ($playerMode !== 'admin' && $playerMode !== 'mobile') {
            $playerVars['controls']    = 0;
            $playerVars['disablekb']   = 1;
        }

        $groupLiveTags  = $this->em->getRepository('MongoboxGroupBundle:GroupLiveTag')->findBy(array('group' => $currentGroup->getId()));

        if ($playerMode === 'admin') {
            if ($currentGroup->getLiveMaxDislikes() ===  null) {
                $maxDislikes = (int) $this->container->getParameter('live_max_dislikes');
            } else {
                $maxDislikes = (int) $currentGroup->getLiveMaxDislikes();
            }

            $tagsList       = $this->em->getRepository('MongoboxJukeboxBundle:VideoTag')->getTagsForGroup($currentGroup);
        } else {
            $maxDislikes    = null;
            $tagsList       = null;
        }

        $playerEvents = array('onStateChange' => 'onPlayerStateChange');

        $websocketsServer   = $request->getSchemeAndHttpHost();
        $websocketsPort     = (int) $this->container->getParameter('websockets_port');

        return array(
            'current_video'     => $currentPlayed,
            'player_mode'       => $playerMode,
            'player_vars'       => json_encode($playerVars),
            'player_events'     => json_encode($playerEvents),
            'player_width'      => $playerWidth,
            'player_height'     => $playerHeight,
            'group'		        => $currentGroup,
            'max_dislikes'      => $maxDislikes,
            'list_tags'	        => $tagsList,
            'groupLiveTags'     => $groupLiveTags,
            'websockets_server'	=> "$websocketsServer:$websocketsPort"
        );
    }
}
