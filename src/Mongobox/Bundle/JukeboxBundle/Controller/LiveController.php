<?php
namespace Mongobox\Bundle\JukeboxBundle\Controller;

use Mongobox\Bundle\JukeboxBundle\Entity\VideoCurrent;
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
	protected function _initJukebox()
	{
		$em = $this->getDoctrine()->getEntityManager();

		$results = $em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->findAll();
		if (count($results) > 0) {
			$currentPlayed = $results[0];

			$currentVideo	= $em->getRepository('MongoboxJukeboxBundle:Videos')->findOneby(array(
				'id' => $currentPlayed->getId()
			));

			$votes = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($currentPlayed);
			$currentVideo->setVotes($currentVideo->getVotes() + $votes);

			$em->getRepository('MongoboxJukeboxBundle:Vote')->wipe($currentPlayed->getId()->getId());
		}

		$em->getRepository('MongoboxJukeboxBundle:Playlist')->generate();

		$nextInPlaylist = $em->getRepository('MongoboxJukeboxBundle:Playlist')->next(1);
		$nextVideoId = $nextInPlaylist->getVideo();

		$em->remove($nextInPlaylist);
		$em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->wipe();

		$current = new VideoCurrent();
		$current->setId($nextVideoId);
		$current->setDate(new \Datetime());

		$em->persist($current);
		$em->flush();

		return $current;
	}

	/**
	 * Retrieves scores of the video
	 *
	 * @param int $videoId
	 * @return array
	 */
	protected function _getVideoScores($videoId)
	{
		$em = $this->getDoctrine()->getEntityManager();

		$upVotes = count($em->getRepository('MongoboxJukeboxBundle:Vote')->findBy(array(
				'video'	=> $videoId,
				'sens'	=> self::UP_VOTE_VALUE,
		)));

		$downVotes	= count($em->getRepository('MongoboxJukeboxBundle:Vote')->findBy(array(
				'video'	=> $videoId,
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
    	$em = $this->getDoctrine()->getEntityManager();
    	$results = $em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->findAll();

    	if (count($results) > 0) {
    		$currentPlayed = $results[0];
    	} else {
    		$currentPlayed = $this->_initJukebox();
    	}

		$currentVideo	= $em->getRepository('MongoboxJukeboxBundle:Videos')->findOneby(array(
			'id' => $currentPlayed->getId()
		));

		// TODO: define users permissions
		$playerMode = $request->get('mode') ? $request->get('mode') : 'showOnly';

		$currentDate	= new \DateTime();
		$startDate		= $currentPlayed->getDate();

		$secondsElapsed = $currentDate->getTimestamp() - $startDate->getTimestamp();
		if ($secondsElapsed < $currentVideo->getDuration()) {
			$playerStart = $secondsElapsed;
		} else {
			$playerStart = 0;
		}

		if ($playerMode != 'admin') {
			$playerVars		= "{ controls: 0, disablekb: 1, start: $playerStart, autoplay: 1 }";
			$playerEvents	= '{ onStateChange: onPlayerStateChange }';
		} else {
			$playerVars		= "{ start: $playerStart, autoplay: 1 }";
			$playerEvents	= '{ onStateChange: onPlayerStateChange }';
		}

    	return array(
    		'page_title'	=> 'Jukebox - Live stream',
    		'current_video'	=> $currentVideo,
    		'player_mode'	=> $playerMode,
    		'player_vars'	=> $playerVars,
    		'player_events'	=> $playerEvents,
    		'socket_params'	=> "ws://{$_SERVER['HTTP_HOST']}:8001"
    	);
    }

    /**
     * @Route("/next", name="live_next")
     */
    public function nextAction(Request $request)
    {
    	$em = $this->getDoctrine()->getEntityManager();

    	$currentPlayed	= $this->_initJukebox();
    	$currentVideo	= $em->getRepository('MongoboxJukeboxBundle:Videos')->findOneby(array(
			'id' => $currentPlayed->getId()
    	));

    	$response = new Response(json_encode(array('nextVideo' => $currentVideo->getLien())));
    	return $response;
    }

    /**
     * @Route("/vote", name="live_vote")
     */
    public function voteAction(Request $request)
    {
    	$em = $this->getDoctrine()->getEntityManager();

    	$videoId		= $request->get('video');
    	$voteType		= $request->get('vote');
    	$currentVideo	= $request->get('currentVideo') ? (int) $request->get('currentVideo') : 0;

    	$currentVideo	= $em->getRepository('MongoboxJukeboxBundle:Videos')->findOneby(array('lien' => $videoId));
    	if (is_null($currentVideo) || !in_array($voteType, array('up', 'down'))) {
    		return new Response();
    	}

    	$oldVote = $em->getRepository('MongoboxJukeboxBundle:Vote')->findOneBy(array(
			'ip'	=> $_SERVER['REMOTE_ADDR'],
			'video'	=> $currentVideo->getId(),
    	));

    	if (!is_null($oldVote)) {
    		$em->remove($oldVote);
    		$em->flush();
    	}

    	$vote = new Vote();
    	$vote->setIp($_SERVER['REMOTE_ADDR']);
    	$vote->setSens(($request->get('vote') === 'up') ? self::UP_VOTE_VALUE : self::DOWN_VOTE_VALUE);
    	$vote->setVideo($currentVideo);

    	$em->persist($vote);
    	$em->flush();

    	if ($currentVideo === 1) {
    		$currentPlayed = $em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->findAll();

    		$currentVideo = $currentPlayed[0];
    		$currentVideo->getId()->setVotes($currentVideo[0]->getId()->getVotes() + $vote->getSens());

    		$em->persist($currentVideo);
    		$em->flush();
    	}

    	$data = $this->_getVideoScores($currentVideo->getId());

    	$response = new Response(json_encode($data));
    	return $response;
    }

    /**
     * @Route("/score", name="live_score")
     */
    public function scoreAction(Request $request)
    {
    	$em = $this->getDoctrine()->getEntityManager();

    	$videoId		= $request->get('video');
    	$currentVideo	= $em->getRepository('MongoboxJukeboxBundle:Videos')->findOneby(array('lien' => $videoId));

    	if (is_null($currentVideo)) {
    		return new Response();
    	}

    	$data = $this->_getVideoScores($currentVideo->getId());

    	$response = new Response(json_encode($data));
    	return $response;
    }
}
