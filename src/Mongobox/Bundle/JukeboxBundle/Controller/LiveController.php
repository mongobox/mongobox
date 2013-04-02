<?php
namespace Mongobox\Bundle\JukeboxBundle\Controller;

use Mongobox\Bundle\JukeboxBundle\Entity\Videos;
use Mongobox\Bundle\JukeboxBundle\Entity\Vote;
use Mongobox\Bundle\JukeboxBundle\Form\ReplaceVideo;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Live stream controller
 *
 * @Route("/live")
 */
class LiveController extends Controller
{
	const UP_VOTE_VALUE		= 1;
	const DOWN_VOTE_VALUE	= -1;

	/**
	 * Initialize Jukebox and return the current video
	 *
	 * @return string
	 */
	protected function _initJukebox($group)
	{
		$em = $this->getDoctrine()->getManager();

		/*$results = $em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->findAll();
		if (count($results) > 0)
		{
			$currentPlayed = $results[0];

			$currentVideo	= $em->getRepository('MongoboxJukeboxBundle:Videos')->findOneby(array(
				'id' => $currentPlayed->getId()
			));

			$votes = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($currentPlayed);
			$currentVideo->setVotes($currentVideo->getVotes() + $votes);

			$em->getRepository('MongoboxJukeboxBundle:Vote')->wipe($currentPlayed->getId()->getId());
		}*/

		$em->getRepository('MongoboxJukeboxBundle:Playlist')->generate($group);

		//On supprime la vidéo en cours
		$playlist_current = $em->getRepository('MongoboxJukeboxBundle:Playlist')->findOneBy(array('group' => $group->getId(), 'current' => 1));
		$votes = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($playlist_current);

		$playlist_current->getVideoGroup()->setVotes($playlist_current->getVideoGroup()->getVotes() + $votes);
		$playlist_current->getVideoGroup()->setDiffusion($playlist_current->getVideoGroup()->getDiffusion() + 1);

		$em->getRepository('MongoboxJukeboxBundle:Vote')->wipe($playlist_current);
		$em->remove($playlist_current);

		//On cherche la prochaine vidéo
		$nextInPlaylist = $em->getRepository('MongoboxJukeboxBundle:Playlist')->next(1, $group);

		$nextInPlaylist->setCurrent(1);
		$nextInPlaylist->getVideoGroup()->setLastBroadcast(new \Datetime());
		$em->flush();

		return $nextInPlaylist;
	}

	/**
	 * Retrieves scores of the playlist
	 *
	 * @param int $playlistId
	 * @return array
	 */
	protected function _getPlaylistScores($playlistId)
	{
		$em = $this->getDoctrine()->getManager();

		$upVotes = count($em->getRepository('MongoboxJukeboxBundle:Vote')->findBy(array(
				'playlist'	=> $playlistId,
				'sens'	=> self::UP_VOTE_VALUE,
		)));

		$downVotes	= count($em->getRepository('MongoboxJukeboxBundle:Vote')->findBy(array(
				'playlist'	=> $playlistId,
				'sens'	=> self::DOWN_VOTE_VALUE,
		)));

		$votesRatio	= $upVotes * self::UP_VOTE_VALUE + $downVotes * self::DOWN_VOTE_VALUE;
		$totalVotes	= $upVotes + $downVotes;

		$data = array(
				'upVotes'		=> $upVotes,
				'downVotes'		=> $downVotes,
				'votesRatio'	=> $votesRatio,
				'totalVotes'	=> $totalVotes
		);

		return $data;
	}

	/**
     * @Route("/", name="live")
     * @Template()
     */
    public function indexAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
		$session = $request->getSession();
		$group = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));
    	$video_en_cours = $em->getRepository('MongoboxJukeboxBundle:Playlist')->findOneBy(array('group' => $group->getId(), 'current' => 1));

    	if (is_object($video_en_cours)) {
    		$currentPlayed = $video_en_cours;
    	} else {
    		$currentPlayed = $this->_initJukebox($group);
    	}

		// TODO: define users permissions
		$playerMode = $request->get('mode') ? $request->get('mode') : 'showOnly';

		$currentDate	= new \DateTime();
		$startDate		= $currentPlayed->getVideoGroup()->getLastBroadcast();

		$secondsElapsed = $currentDate->getTimestamp() - $startDate->getTimestamp();
		if ($secondsElapsed < $currentPlayed->getVideoGroup()->getVideo()->getDuration()) {
			$playerStart = $secondsElapsed;
		} else {
			$playerStart = 0;
		}

        $playerVars = array(
            'start'     => $playerStart,
            'autoplay'  => 1,
            'volume'    => $currentPlayed->getVideoGroup()->getVideo()->getVolume()
        );

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

        $playerEvents = array('onStateChange' => 'onPlayerStateChange');

    	return array(
    		'page_title'	=> 'Jukebox - Live stream',
    		'current_video'	=> $currentPlayed,
    		'player_mode'	=> $playerMode,
    		'player_vars'	=> json_encode($playerVars),
    		'player_events'	=> json_encode($playerEvents),
			'player_width'	=> $playerWidth,
			'player_height'	=> $playerHeight,
    		'socket_params'	=> "ws://{$_SERVER['HTTP_HOST']}:8001"
    	);
    }

    /**
     * @Route("/next", name="live_next")
     * @Method("POST")
     */
    public function nextAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();

		$session    = $request->getSession();
        $group      = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));

        $currentPlaylist = $em
            ->getRepository('MongoboxJukeboxBundle:Playlist')
            ->findOneBy(array(
                'group'     => $group->getId(),
                'current'   => 1
            ))
        ;

        $currentVideo   = $currentPlaylist->getVideoGroup()->getVideo();
        $videoVolume    = (int) $request->get('volume', 50);

        $currentVideo->setVolume($videoVolume);
        $em->persist($currentVideo);
        $em->flush();

    	$currentPlayed	= $this->_initJukebox($group);

    	$response = new Response(json_encode(array(
            'videoId'       => $currentPlayed->getVideoGroup()->getVideo()->getLien(),
            'playlistId'    => $currentPlayed->getId(),
            'videoVolume'   => $currentPlayed->getVideoGroup()->getVideo()->getVolume()
        )));

    	return $response;
    }

    /**
     * @Route("/vote", name="live_vote")
     */
    public function voteAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
		$session = $request->getSession();
		$group = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));
		$user = $this->get('security.context')->getToken()->getUser();

    	$playlistId		= $request->get('playlist');
    	$voteType		= $request->get('vote');

    	$currentPlaylist = $em->getRepository('MongoboxJukeboxBundle:Playlist')->findOneBy(array('group' => $group->getId(), 'current' => 1));
    	if (is_null($currentPlaylist) || !in_array($voteType, array('up', 'down'))) {
    		return new Response();
    	}

    	$oldVote = $em->getRepository('MongoboxJukeboxBundle:Vote')->findOneBy(array(
			'user'	=> $user->getId(),
			'playlist'	=> $playlistId
    	));

    	if (!is_null($oldVote)) {
    		$em->remove($oldVote);
    		$em->flush();
    	}

    	$vote = new Vote();
    	$vote->setUser($user);
    	$vote->setSens(($request->get('vote') === 'up') ? self::UP_VOTE_VALUE : self::DOWN_VOTE_VALUE);
    	$vote->setPlaylist($currentPlaylist);

    	$em->persist($vote);
    	$em->flush();

    	$data = $this->_getPlaylistScores($currentPlaylist->getId());

    	$response = new Response(json_encode($data));
    	return $response;
    }

    /**
     * @Route("/score", name="live_score")
     */
    public function scoreAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();

    	$playlist		= $request->get('playlist');
    	$currentPlaylist	= $em->getRepository('MongoboxJukeboxBundle:Playlist')->find($playlist);

    	if (is_null($currentPlaylist)) {
    		return new Response();
    	}

    	$data = $this->_getPlaylistScores($currentPlaylist->getId());

    	$response = new Response(json_encode($data));
    	return $response;
    }

    /**
     * @Route("/replace", name="live_replace")
     * @Template()
     */
    public function replaceAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($request->getMethod() === 'POST') {
            $newVideo = new Videos();

            $editForm = $this->createForm(new ReplaceVideo(), $newVideo);
            $editForm->bind($request);

            $video = $em->getRepository('MongoboxJukeboxBundle:Videos')->find($newVideo->getId());
            if ($editForm->isValid() === true && $video !== null) {
                try {
                    $video->setTitle($newVideo->getTitle());
                    $video->setLien($newVideo->getLien());
                    $video->setArtist($newVideo->getArtist());
                    $video->setSongName($newVideo->getSongName());

                    $em->persist($video);
                    $em->flush();

                    $message = 'Mise à jour de la vidéo effectuée avec succès.';

                } catch (Exception $e) {
                    $editForm->addError(new FormError('Une erreur s\'est produite durant la sauvegarde.'));
                }
            } else {
                $editForm->addError(new FormError('Les données du formulaire ne sont pas valides.'));
            }
        } else {
            $session = $request->getSession();

            $currentGroup       = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));
            $currentPlaylist    = $em->getRepository('MongoboxJukeboxBundle:Playlist')->findOneBy(array(
                'group'     => $currentGroup->getId(),
                'current'   => 1
            ));

            if ($currentVideo = $currentPlaylist->getVideoGroup()->getVideo()) {
                $editForm = $this->createForm(new ReplaceVideo(), $currentVideo);
            } else {
                $editForm = $this->createForm(new ReplaceVideo());
                $editForm->addError(new FormError('Impossible de récupérer la vidéo courante dans la playlist.'));
            }
        }

        return array(
            'message'  => isset($message) ? $message : null,
            'edit_form' => $editForm->createView()
        );
    }
}
