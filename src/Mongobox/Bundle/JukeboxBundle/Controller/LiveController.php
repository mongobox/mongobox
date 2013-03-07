<?php
namespace Mongobox\Bundle\JukeboxBundle\Controller;

use Mongobox\Bundle\JukeboxBundle\Entity\Vote;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

		if($playerMode == 'mobile')  {
			$playerWidth 	= '390';
			$playerHeight	= '220';
			
			$playerVars		= '{ start: '. $playerStart.', autoplay: 1,mode:"opaque" }';
			$playerEvents	= '{ onStateChange: onPlayerStateChange }';
		}
		elseif ($playerMode != 'admin' && $playerMode !='mobile') {
			$playerWidth 	= '800px';
			$playerHeight	= '500px';
			
			$playerVars		= "{ controls: 0, disablekb: 1, start: $playerStart, autoplay: 1 }";
			$playerEvents	= '{ onStateChange: onPlayerStateChange }';
		} 
		else {
			$playerWidth 	= '800px';
			$playerHeight	= '500px';
			
			$playerVars		= "{ start: $playerStart, autoplay: 1 }";
			$playerEvents	= '{ onStateChange: onPlayerStateChange }';
		}

    	return array(
    		'page_title'	=> 'Jukebox - Live stream',
    		'current_video'	=> $currentPlayed,
    		'player_mode'	=> $playerMode,
    		'player_vars'	=> $playerVars,
    		'player_events'	=> $playerEvents,
			'player_width'	=> $playerWidth,
			'player_height'	=> $playerHeight,
    		'socket_params'	=> "ws://{$_SERVER['HTTP_HOST']}:8001"
    	);
    }

    /**
     * @Route("/next", name="live_next")
     */
    public function nextAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
		$session = $request->getSession();
		$group = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));

    	$currentPlayed	= $this->_initJukebox($group);

    	$response = new Response(json_encode(array('videoId' => $currentPlayed->getVideoGroup()->getVideo()->getLien(), 'playlistId' => $currentPlayed->getId())));
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
}
