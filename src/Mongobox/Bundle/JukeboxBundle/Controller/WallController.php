<?php

namespace Mongobox\Bundle\JukeboxBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Mongobox\Bundle\JukeboxBundle\Entity\Videos;
use Mongobox\Bundle\JukeboxBundle\Entity\Playlist;
use Mongobox\Bundle\JukeboxBundle\Entity\Vote;
use Mongobox\Bundle\JukeboxBundle\Entity\VideoCurrent;
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
		$userDb = $em->getRepository('EmakinaLdapBundle:User')->findOneByTrigramme($user->getUsername());
		
        $video = new Videos();
        $form = $this->createForm(new VideoType(), $video);
        $total_video = count($em->getRepository('MongoboxJukeboxBundle:Videos')->findAll());
        $playlist = $em->getRepository('MongoboxJukeboxBundle:Playlist')->next(10);
        $videos_historique = $em->createQuery('SELECT v FROM MongoboxJukeboxBundle:Videos v ORDER BY v.lastBroadcast DESC')->setMaxResults(5)->getResult();
        $video_en_cours = $em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->findAll();
        $somme = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($video_en_cours[0]->getId());
        $somme_pl = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeAllVotes();

        if ( 'POST' === $request->getMethod() ) {
            $form->bindRequest($request);
            if ( $form->isValid() ) {
                $video->setLien(Videos::parse_url_detail($video->getLien()));
                //On vérifie qu'elle n'existe pas déjà
                $isVideo = $em->getRepository('MongoboxJukeboxBundle:Videos')->findOneby(array('lien' => $video->getLien()));
                if (!is_object($isVideo)) {
                    $dataYt = $video->getDataFromYoutube();

                    $video->setDate(new \Datetime())
							->setDone(0)
							->setTitle( $dataYt->title )
							->setAddressIp( $_SERVER['REMOTE_ADDR'])
							->setDiffusion(0)
							->setVendredi(0)
							->setDuration($dataYt->duration)
                            ->setThumbnail( $dataYt->thumbnail->hqDefault )
                            ->setThumbnailHq( $dataYt->thumbnail->sqDefault )
							->setuser($userDb);
                    $em->persist($video);
                    $em->flush();

                    //On l'ajoute à la playlist
                    $playlist_add = new Playlist();
                    $playlist_add->setVideo($video);
                    $playlist_add->setRandom(0);
                    $playlist_add->setDate(new \Datetime());
                    $em->persist($playlist_add);

                    $em->flush();
                    $this->get('session')->setFlash('success', 'Vidéo "'.$dataYt->title .'" postée avec succès');
                } else {
                    $this->get('session')->setFlash('success', 'Cette vidéo existe déjà');
                }

                return $this->redirect($this->generateUrl('wall_index'));
            }
        }

        return array
        (
            'form' => $form->createView(),
            'total_video' => $total_video,
            'playlist' => $playlist,
            'videos_historique' => $videos_historique,
            'video_en_cours' => ( isset($video_en_cours[0]) ) ? $video_en_cours[0] : null,
            'date_actuelle' => new \Datetime(),
            'somme' => $somme,
            'somme_pl' => $somme_pl
        );
    }

    /**
     * @Template()
     * @Route( "/jukebox", name="jukebox")
     */
    public function jukeboxAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $video_en_cours = $em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->findAll();
        if (count($video_en_cours) > 0) {
            $total_vote = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($video_en_cours[0]->getId());
            $video_en_cours[0]->getId()->setVotes($video_en_cours[0]->getId()->getVotes() + $total_vote);
            //On wipe les votes de la vidéo d'avant !
            $em->getRepository('MongoboxJukeboxBundle:Vote')->wipe($video_en_cours[0]->getId()->getId());
        }

        //On regénère la playlist
        $em->getRepository('MongoboxJukeboxBundle:Playlist')->generate();

        //On va chercher la prochaine vidéo de la playlist
        $playlist_next = $em->getRepository('MongoboxJukeboxBundle:Playlist')->next(1);
        $video = $playlist_next->getVideo();

        $video->setDiffusion($video->getDiffusion() + 1);
        $video->setDone(1);
        $video->setLastBroadcast(new \Datetime()); // date de diffusion

        //On la supprime de la playlist
        $em->remove($playlist_next);

        $total_video = count($em->getRepository('MongoboxJukeboxBundle:Videos')->findAll());
        $playlist = $em->getRepository('MongoboxJukeboxBundle:Playlist')->next(10);

        //$video_old = $em->getRepository('MongoboxJukeboxBundle:Videos')->next();
        //$video = $em->getRepository('MongoboxJukeboxBundle:Videos')->find($video_old->getId());

        $em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->wipe();

        $current = new VideoCurrent();
        $current->setId($video);
        $current->setDate(new \Datetime());

        $em->persist($current);
        $em->flush();

        return array
        (
            'next_video' => $video->getLien(),
            'id_video' => $video->getId(),
            'video' => $video,
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
        $em = $this->getDoctrine()->getEntityManager();
        $video_current = $em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->findOneBy(array('id_video' => $id_video));
        $video = $em->getRepository('MongoboxJukeboxBundle:Videos')->find($id_video);

        //On wipe les votes pour cette vidéo
        $em->getRepository('MongoboxJukeboxBundle:Vote')->wipe($video->getId());

        $em->remove($video_current);
        $em->flush();
        $em->remove($video);
        $em->flush();

        return $this->redirect($this->generateUrl('jukebox'));
    }

    /**
     * @Template()
     * @Route( "/vote/{id}/{sens}/{current}", name="vote")
     * @ParamConverter("video", class="MongoboxJukeboxBundle:Videos")
     * 
     */
    public function voteAction(Request $request, $video, $sens, $current = false)
    {
		$em = $this->getDoctrine()->getEntityManager();
		
		$user = $this->get('security.context')->getToken()->getUser();
		$userDb = $em->getRepository('EmakinaLdapBundle:User')->findOneByTrigramme($user->getUsername());
		
        //Wipe de son ancien vote
        $old_vote = $em->getRepository('MongoboxJukeboxBundle:Vote')
						->findOneBy(array(
							'user'	=> $userDb,
							'video' => $video, 
							'ip' => $this->getRequest()->server->get('REMOTE_ADDR')
							)
						);
		if (!is_null($old_vote)) {
            $em->remove($old_vote);
            $em->flush();
        }
		
        $vote = new Vote();
        $vote->setIp($this->getRequest()->server->get('REMOTE_ADDR'))
				->setSens($sens)
				->setVideo($video)
				->setUser($userDb);

        if ($current) {
            $video_en_cours = $em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->findAll();
            $video_en_cours[0]->getId()->setVotes($video_en_cours[0]->getId()->getVotes() + (int) $sens);
        }

        $em->persist($vote);
        $em->flush();

        return new Response($em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($video));
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
        $video_en_cours = $em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->findAll();
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
        $video_en_cours = $em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->findAll();
        $somme = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($video_en_cours[0]->getId());

        return array(
            'video_en_cours' => $video_en_cours[0],
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
        $total_video = count($em->getRepository('MongoboxJukeboxBundle:Videos')->findAll());
        $playlist = $em->getRepository('MongoboxJukeboxBundle:Playlist')->next(10);
        $videos_historique = $em->createQuery('SELECT v FROM MongoboxJukeboxBundle:Videos v ORDER BY v.lastBroadcast DESC')->setMaxResults(5)->getResult();
        $video_en_cours = $em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->findAll();
        $somme_pl = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeAllVotes();

        return array(
            'video_en_cours' => $video_en_cours[0],
            'total_video' => $total_video,
            'playlist' => $playlist,
            'videos_historique' => $videos_historique,
            'somme_pl' => $somme_pl
        );
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