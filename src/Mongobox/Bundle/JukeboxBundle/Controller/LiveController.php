<?php
namespace Mongobox\Bundle\JukeboxBundle\Controller;

use Mongobox\Bundle\JukeboxBundle\Entity\Videos;
use Mongobox\Bundle\JukeboxBundle\Entity\VideoTag;
use Mongobox\Bundle\JukeboxBundle\Entity\Volume;
use Mongobox\Bundle\JukeboxBundle\Entity\Vote;
use Mongobox\Bundle\GroupBundle\Entity\Group;
use Mongobox\Bundle\GroupBundle\Entity\GroupLiveTag;
use Mongobox\Bundle\JukeboxBundle\Form\ReplaceVideo;
use Mongobox\Bundle\JukeboxBundle\Form\VideoTagsType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
    const VOLUME_STEP		= 10;

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
		//Il n'y a pas de vidéo en cours, on va en chercher une
		if(!is_null($playlist_current))
		{
			$votes = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($playlist_current);

			$playlist_current->getVideoGroup()->setVotes($playlist_current->getVideoGroup()->getVotes() + $votes);
			$playlist_current->getVideoGroup()->setDiffusion($playlist_current->getVideoGroup()->getDiffusion() + 1);

			$em->getRepository('MongoboxJukeboxBundle:Vote')->wipe($playlist_current);
			$em->getRepository('MongoboxJukeboxBundle:Volume')->wipe($playlist_current);
			$em->remove($playlist_current);
		}

		//On cherche la prochaine vidéo
		$nextInPlaylist = $em->getRepository('MongoboxJukeboxBundle:Playlist')->next(1, $group);

		if(is_object($nextInPlaylist))
		{
			$nextInPlaylist->setCurrent(1);
			$nextInPlaylist->getVideoGroup()->setLastBroadcast(new \Datetime());
		}
		$em->flush();

		return $nextInPlaylist;
	}

	/**
	 * Retrieve scores of the playlist
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
    	$em         = $this->getDoctrine()->getManager();
		$session    = $request->getSession();

		$group          = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));
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
            'start'             => $playerStart,
            'autoplay'          => 1,
            'volume'            => $currentPlayed->getVideoGroup()->getVolume(),
			'iv_load_policy'    => 3,
			'rel'               => 0
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

        if ($playerMode === 'admin') {
            if ($group->getLiveMaxDislikes() ===  null) {
                $maxDislikes = $this->container->getParameter('default_max_dislikes');
            } else {
                $maxDislikes = (int) $group->getLiveMaxDislikes();
            }

			$list_tags      = $em->getRepository('MongoboxJukeboxBundle:VideoTag')->getTagsForGroup($group);
			$groupLiveTags  = $em->getRepository('MongoboxGroupBundle:GroupLiveTag')->findBy(array('group' => $group->getId()));
		} else {
            $maxDislikes    = null;
            $list_tags      = null;
            $groupLiveTags  = null;
        }

        $playerEvents = array('onStateChange' => 'onPlayerStateChange');

    	return array
		(
    		'page_title'	=> 'Jukebox - Live stream',
    		'current_video'	=> $currentPlayed,
    		'player_mode'	=> $playerMode,
    		'player_vars'	=> json_encode($playerVars),
    		'player_events'	=> json_encode($playerEvents),
			'player_width'	=> $playerWidth,
			'player_height'	=> $playerHeight,
    		'socket_params'	=> "ws://{$_SERVER['HTTP_HOST']}:8001",
			'group'			=> $group,
            'max_dislikes'  => $maxDislikes,
			'list_tags'		=> $list_tags,
			'groupLiveTags' => $groupLiveTags
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

        $currentVideo   = $currentPlaylist->getVideoGroup();
        $videoVolume    = (int) $request->get('volume', 50);

        $currentVideo->setVolume($videoVolume);
        $em->persist($currentVideo);
        $em->flush();

    	$currentPlayed	= $this->_initJukebox($group);

    	$response = new Response(json_encode(array(
            'videoId'       => $currentPlayed->getVideoGroup()->getVideo()->getLien(),
            'playlistId'    => $currentPlayed->getId(),
            'videoVolume'   => $currentPlayed->getVideoGroup()->getVolume()
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

    /**
     * Retrieve current volume of the given playlist
     *
     * @param \Mongobox\Bundle\JukeboxBundle\Entity\Playlist $playlist
     * @return array
     */
    protected function _getPlaylistVolume($playlist)
    {
        $em = $this->getDoctrine()->getManager();

        $upVotes = count($em->getRepository('MongoboxJukeboxBundle:Volume')->findBy(array(
            'playlist'	=> $playlist->getId(),
            'direction' => self::UP_VOTE_VALUE,
        )));

        $downVotes = count($em->getRepository('MongoboxJukeboxBundle:Volume')->findBy(array(
            'playlist'	=> $playlist->getId(),
            'direction' => self::DOWN_VOTE_VALUE,
        )));

        $defaultVolume = $playlist->getVideoGroup()->getVolume();

        $currentVolume  = $defaultVolume +  ($upVotes * self::VOLUME_STEP) - ($downVotes * self::VOLUME_STEP);
        $totalVotes     = $upVotes + $downVotes;

        $data = array(
            'upVotes'		=> $upVotes,
            'downVotes'		=> $downVotes,
            'currentVolume'	=> $currentVolume,
            'totalVotes'	=> $totalVotes
        );

        return $data;
    }

    /**
     * @Route("/volume", name="live_volume")
     */
    public function volumeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session    = $request->getSession();
        $group      = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));
        $user       = $this->get('security.context')->getToken()->getUser();

        $playlistId		= $request->get('playlist');
        $voteType		= $request->get('vote');

        $currentPlaylist = $em
            ->getRepository('MongoboxJukeboxBundle:Playlist')
            ->findOneBy(array(
                'group'     => $group->getId(),
                'current'   => 1
            ))
        ;

        if (is_null($currentPlaylist)) {
            return new Response();
        }

        if ($request->getMethod() === 'POST' && in_array($voteType, array('up', 'down'))) {
            $oldVote = $em->getRepository('MongoboxJukeboxBundle:Volume')->findOneBy(array(
                'user'      => $user->getId(),
                'playlist'  => $playlistId
            ));

            if (!is_null($oldVote)) {
                $em->remove($oldVote);
                $em->flush();
            }

            $vote = new Volume();
            $vote->setUser($user);
            $vote->setDirection(($request->get('vote') === 'up') ? self::UP_VOTE_VALUE : self::DOWN_VOTE_VALUE);
            $vote->setPlaylist($currentPlaylist);

            $em->persist($vote);
            $em->flush();
        }

        $data       = $this->_getPlaylistVolume($currentPlaylist);
        $response   = new Response(json_encode($data));

        return $response;
    }

    /**
     * @Route("/live_tag_select/{id}/{id_group}/{selected}", name="live_tag_select")
	 * @ParamConverter("tag", class="MongoboxJukeboxBundle:VideoTag")
	 * @ParamConverter("group", class="MongoboxGroupBundle:Group", options={"id" = "id_group"})
     */
	function liveTagSelectAction(VideoTag $tag, Group $group, $selected)
	{
		$em = $this->getDoctrine()->getManager();

		$glt = new GroupLiveTag();
		$glt->setGroup($group);
		$glt->setSelected((boolean)$selected);
		$glt->setVideoTag($tag);
		$em->persist($glt);

		$em->flush();

		$groupLiveTags = $em->getRepository('MongoboxGroupBundle:GroupLiveTag')->findBy(array('group' => $group->getId(), 'video_tag' => $tag->getId()));
		$button_selected = 0;
		$button_unselected = 0;
		foreach($groupLiveTags as $glt)
		{
			if($glt->getSelected() == 1) $button_selected = 1;
			else $button_unselected = 1;
		}

		$return = array
		(
			'selected' => $selected,
			'html_tag' => $this->render('MongoboxJukeboxBundle:Partial:live-tag-video.html.twig', array(
						'tag' => $glt
					))->getContent(),
			'html_button' => $this->render('MongoboxJukeboxBundle:Partial:live-button-tag-video.html.twig', array(
						'tag' => $tag,
						'group' => $group,
						'selected' => $button_selected,
						'unselected' => $button_unselected
					))->getContent()
		);
		return new Response(json_encode($return));
	}

    /**
     * @Route("/live_tag_delete/{id}", name="live_tag_delete")
	 * @ParamConverter("tag", class="MongoboxGroupBundle:GroupLiveTag")
     */
	function liveTagDeleteAction(GroupLiveTag $tag)
	{
		$em = $this->getDoctrine()->getManager();

		$em->remove($tag);

		$em->flush();

		$return = array('success' => 'ok');
		return new Response(json_encode($return));
	}

    /**
     * @Route("/live_empty_playlist/{id_group}/{force}", name="live_empty_playlist", defaults={"force" = false})
	 * @ParamConverter("group", class="MongoboxGroupBundle:Group", options={"id" = "id_group"})
     */
	function liveEmptyPlaylistAction(Group $group, $force)
	{
		$em = $this->getDoctrine()->getManager();

		$em->getRepository('MongoboxGroupBundle:Group')->emptyPlaylist($group, $force);

		$return = array('success' => 'ok');
		return new Response(json_encode($return));
	}
}
