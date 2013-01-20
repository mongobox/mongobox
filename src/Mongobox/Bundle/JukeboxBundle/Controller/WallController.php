<?php

namespace Mongobox\Bundle\JukeboxBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Mongobox\Bundle\JukeboxBundle\Entity\Videos;
use Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup;
use Mongobox\Bundle\JukeboxBundle\Entity\Playlist;
use Mongobox\Bundle\JukeboxBundle\Entity\Vote;
use Mongobox\Bundle\JukeboxBundle\Form\VideoType;

class WallController extends Controller
{

    protected $feedUrlPP = 'http://www.brain-magazine.com/rss.php';
    protected $feedUrlJDC = 'http://lesjoiesducode.tumblr.com/rss';
    protected $feedUrl4Gifs = 'http://4gifs.tumblr.com/rss';

    protected function _getFeedData($url, $filter_type = false, $filter = false, $limit = 6)
    {
        $feed = @simplexml_load_file( $url );
        $results = array();
        $i = 0;

        if (false !== $feed) {
            foreach ($feed->channel->item as $article) {
                if (!$filter || (string)$article->{$filter_type} == $filter) {
                    $results[] = $article;
                }
                $i++;
                if($i == $limit) break;
            }
        }
        return $results;
    }

    /**
     * @Template()
     * @Route( "/", name="wall_index")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
		$user = $this->get('security.context')->getToken()->getUser();
		$user->getGroups();

		//Si l'utilisateur a au moins un groupe
		if(count($user->getGroups()) > 0)
		{
			$session = $request->getSession();

			//Si on a pas déjà de communauté définie, on va en trouver une
			if(is_null($session->get('id_group')))
			{
				//On regarde si ya un cookie
				if($request->cookies->has('id_group'))
				{
					$id_group = $request->cookies->get('id_group');
				}
				else
				{
					$id_group = $user->getGroupDefault();
				}
				$session->set('id_group', $id_group);
			}
			//$userDb = $em->getRepository('EmakinaLdapBundle:User')->findOneByTrigramme($user->getUsername());
			$group = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));

			$video = new Videos();
			$form = $this->createForm(new VideoType(), $video);
			$total_video = count($em->getRepository('MongoboxJukeboxBundle:Videos')->findGroupAll($group));
			$playlist = $em->getRepository('MongoboxJukeboxBundle:Playlist')->next(10, $group);
			$videos_historique = $em->getRepository('MongoboxJukeboxBundle:VideoGroup')->findLast(5, $group);
			$video_en_cours = $em->getRepository('MongoboxJukeboxBundle:Playlist')->findOneBy(array('group' => $group->getId(), 'current' => 1));
			if(is_object($video_en_cours)) $somme = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($video_en_cours->getId());
			else $somme = 0;
			$somme_pl = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeAllVotes();

			if ( 'POST' === $request->getMethod() )
			{
				$form->bindRequest($request);
				if ( $form->isValid() )
				{
					$video->setLien(Videos::parse_url_detail($video->getLien()));
					//On vérifie qu'elle n'existe pas déjà
					$video_new = $em->getRepository('MongoboxJukeboxBundle:Videos')->findOneby(array('lien' => $video->getLien()));
					if (!is_object($video_new))
					{
						$dataYt = $video->getDataFromYoutube();

						$video->setDate(new \Datetime())
								->setTitle( $dataYt->title )
								->setDuration($dataYt->duration)
								->setThumbnail( $dataYt->thumbnail->hqDefault )
								->setThumbnailHq( $dataYt->thumbnail->sqDefault );
						$em->persist($video);
						$em->flush();
						$video_new = $video;

						$this->get('session')->setFlash('success', 'Vidéo "'.$dataYt->title .'" postée avec succès');
					}
					//On vérifie qu'elle n'existe pas pour ce groupe
					$video_group = $em->getRepository('MongoboxJukeboxBundle:VideoGroup')->findOneby(array('video' => $video_new, 'group' => $group));
					if(!is_object($video_group))
					{
						$video_group = new VideoGroup();
						$video_group->setVideo($video_new)
									->setGroup($group)
									->setUser($user)
									->setDiffusion(0)
									->setVendredi(0)
									->setVolume(100)
									->setVotes(0);
						$em->persist($video_group);
						$em->flush();
					}
					//On l'ajoute à la playlist
					$playlist_add = new Playlist();
					$playlist_add->setVideoGroup($video_group)
									->setGroup($group)
									->setDate(new \Datetime())
									->setRandom(0)
									->setCurrent(0);
					$em->persist($playlist_add);

					$em->flush();

					return $this->redirect($this->generateUrl('wall_index'));
				}
			}

			return array
			(
				'form' => $form->createView(),
				'total_video' => $total_video,
				'playlist' => $playlist,
				'videos_historique' => $videos_historique,
				'video_en_cours' => $video_en_cours,
				'date_actuelle' => new \Datetime(),
				'somme' => $somme,
				'somme_pl' => $somme_pl
			);
		}
		//Si l'utilisateur n'a pas de groupe, on propose une liste de group publics
		else
		{
			return $this->redirect($this->generateUrl('group_index'));
		}
    }

    /**
     * @Template()
     * @Route( "/jukebox", name="jukebox")
     */
    public function jukeboxAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
		$session = $request->getSession();
		$group = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));

        $video_en_cours = $em->getRepository('MongoboxJukeboxBundle:Playlist')->findOneBy(array('group' => $group->getId(), 'current' => 1));
        if (count($video_en_cours) > 0)
		{
            $total_vote = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($video_en_cours->getId());
            $video_en_cours->getVideoGroup()->setVotes($video_en_cours->getVideoGroup()->getVotes() + $total_vote);
            //On wipe les votes de la vidéo d'avant !
            $em->getRepository('MongoboxJukeboxBundle:Vote')->wipe($video_en_cours->getId());
        }

        //On regénère la playlist
        $em->getRepository('MongoboxJukeboxBundle:Playlist')->generate($group);

        //On va chercher la prochaine vidéo de la playlist
        $playlist_next = $em->getRepository('MongoboxJukeboxBundle:Playlist')->next(1, $group);
		$playlist_next->setCurrent(1);

        $video_group = $playlist_next->getVideoGroup();
        $video_group->setDiffusion($video_group->getDiffusion() + 1);
        $video_group->setLastBroadcast(new \Datetime()); // date de diffusion

        //On la supprime de la playlist
        if(is_object($video_en_cours))
		{
			$em->remove($video_en_cours);
		}

        $total_video = count($em->getRepository('MongoboxJukeboxBundle:Videos')->findAll());
        $playlist = $em->getRepository('MongoboxJukeboxBundle:Playlist')->next(10, $group);

        $em->flush();

        return array
        (
            'video_group' => $video_group,
            'total_video' => $total_video,
            'playlist' => $playlist
        );
    }

    /**
     * @Template()
     * @Route( "/next", name="next_video")
     */
    public function nextAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $em->getRepository('MongoboxJukeboxBundle:Playlist')->generate();

        return new Response();
    }

    /**
     * @Template()
     * @Route( "/delete/{id_video}", name="delete_video")
     */
    public function deleteAction(Request $request, $id_video)
    {
        /*$em = $this->getDoctrine()->getEntityManager();
        $video_current = $em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->findOneBy(array('id_video' => $id_video));
        $video = $em->getRepository('MongoboxJukeboxBundle:Videos')->find($id_video);

        //On wipe les votes pour cette vidéo
        $em->getRepository('MongoboxJukeboxBundle:Vote')->wipe($video->getId());

        $em->remove($video_current);
        $em->flush();
        $em->remove($video);
        $em->flush();

        return $this->redirect($this->generateUrl('jukebox'));*/
    }

    /**
     * @Template()
     * @Route( "/vote/{id}/{sens}", name="vote")
     * @ParamConverter("playlist", class="MongoboxJukeboxBundle:Playlist")
     * 
     */
    public function voteAction(Request $request, $playlist, $sens)
    {
		$em = $this->getDoctrine()->getEntityManager();
		
		$user = $this->get('security.context')->getToken()->getUser();
		
        //Wipe de son ancien vote
        $old_vote = $em->getRepository('MongoboxJukeboxBundle:Vote')
						->findOneBy(array(
							'user'	=> $user,
							'playlist' => $playlist,
							)
						);
		if (!is_null($old_vote)) {
            $em->remove($old_vote);
            $em->flush();
        }

		if($sens != 0)
		{
			$vote = new Vote();
			$vote->setSens($sens)
					->setPlaylist($playlist)
					->setUser($user);

			$em->persist($vote);
			$em->flush();
		}

        return new Response($em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($playlist));
    }

    /**
     * @Template()
     * @Route( "/ajax_flag_vendredi_video/{id_video}", name="ajax_flag_vendredi_video")
     */
    public function ajaxFlagVendrediVideoAction(Request $request, $id_video)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $video = $em->getRepository('MongoboxJukeboxBundle:Videos')->find($id_video);
        $video->setVendredi(1);
        $em->flush();

        return new Response();
    }

    /**
     *
     * @Route( "/ajax_is_vote_next", name="ajax_is_vote_next")
     */
    public function ajaxIsVoteNext(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        /*$video_en_cours = $em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->findAll();*/
        $somme = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($video_en_cours[0]->getId());
        if($somme <= -2) $is_next = true;
        else $is_next = false;
        return new Response(json_encode(array('next' => $is_next)));
    }

    /**
     * @Template()
     * @Route( "/infos/{id_video}", name="infos_video")
     */
    public function infosAction(Request $request, $id_video)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $video = $em->getRepository('MongoboxJukeboxBundle:Videos')->find($id_video);
        $feed = 'http://gdata.youtube.com/feeds/api/videos/'.$video->getLien();
        $xml = simplexml_load_file($feed);
        echo '<pre>';
        var_dump($xml);
        exit;

        return array();
    }

    /**
     * @Template("MongoboxJukeboxBundle:Wall/Blocs:videoEnCours.html.twig")
     * @Route( "/video_en_cours", name="video_en_cours")
     */
    public function videoEnCoursAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
		$group = $em->getRepository('MongoboxGroupBundle:Group')->find(1);
		$video_en_cours = $em->getRepository('MongoboxJukeboxBundle:Playlist')->findOneBy(array('group' => $group->getId(), 'current' => 1));
		if(is_object($video_en_cours)) $somme = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($video_en_cours->getId());
		else $somme = 0;

        return array(
            'video_en_cours' => $video_en_cours,
            'date_actuelle' => new \Datetime(),
            'somme' => $somme
        );
    }

    /**
     * @Template("MongoboxJukeboxBundle:Wall/Blocs:statistiques.html.twig")
     * @Route( "/statistiques", name="statistiques")
     */
    public function statistiquesAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
		$user = $this->get('security.context')->getToken()->getUser();
		$user->getGroups();

		//Si l'utilisateur a au moins un groupe
		if(count($user->getGroups()) > 0)
		{
			//$userDb = $em->getRepository('EmakinaLdapBundle:User')->findOneByTrigramme($user->getUsername());
			$group = $em->getRepository('MongoboxGroupBundle:Group')->find(1);

			$video = new Videos();
			$form = $this->createForm(new VideoType(), $video);
			$total_video = count($em->getRepository('MongoboxJukeboxBundle:Videos')->findGroupAll($group));
			$playlist = $em->getRepository('MongoboxJukeboxBundle:Playlist')->next(10, $group);
			$videos_historique = $em->getRepository('MongoboxJukeboxBundle:VideoGroup')->findLast(5, $group);
			$video_en_cours = $em->getRepository('MongoboxJukeboxBundle:Playlist')->findOneBy(array('group' => $group->getId(), 'current' => 1));
			if(is_object($video_en_cours)) $somme = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($video_en_cours->getId());
			else $somme = 0;
			$somme_pl = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeAllVotes();

			return array(
				'video_en_cours' => $video_en_cours,
				'total_video' => $total_video,
				'playlist' => $playlist,
				'videos_historique' => $videos_historique,
				'somme_pl' => $somme_pl
			);
		}
    }

    /**
     * @Template("MongoboxJukeboxBundle:Wall/Blocs:fluxRss.html.twig")
     * @Route( "/flux_rss", name="flux_rss")
     */
    public function fluxRssAction(Request $request)
    {
        $flux_rss = array(
            array(
                'title' => 'Page Pute',
                'items' => $this->_getFeedData($this->feedUrlPP, 'category', 'Page Pute'),
                'link' => true,
                'description' => false
            ),
            array(
                'title' => 'Les Joies du Code',
                'items' => $this->_getFeedData($this->feedUrlJDC),
                'link' => true,
                'description' => false
            ),
            array(
                'title' => '4Gifs',
                'items' => $this->_getFeedData($this->feedUrl4Gifs),
                'link' => false,
                'description' => true
            )
        );

        return array
        (
            'flux_rss' => $flux_rss
        );
    }
}