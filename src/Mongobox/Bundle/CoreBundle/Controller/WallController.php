<?php

namespace Mongobox\Bundle\CoreBundle\Controller;

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
use Mongobox\Bundle\JukeboxBundle\Form\Type\VideoType;

/**
 * Class WallController
 *
 * @category    Mongobox
 * @package     Mongobox\Bundle\CoreBundle\Controller
 */
class WallController extends Controller
{
    /**
     * @Template()
     * @Route( "/", name="wall_index")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $user->getGroups();

        //Si l'utilisateur a au moins un groupe
        if (count($user->getGroups()) > 0) {
            $session = $request->getSession();

            //Si on a pas déjà de groupe défini, on va en trouver une
            if (is_null($session->get('id_group'))) {
                //On regarde si ya un cookie
                if ($request->cookies->has('id_group')) {
                    $id_group = $request->cookies->get('id_group');
                } else {
                    $id_group = $user->getGroupDefault();
                }
                $session->set('id_group', $id_group);
            } else {
                $id_group = $session->get('id_group');
            }
            $playlist = $em->getRepository('MongoboxJukeboxBundle:Playlist')->next(10, $id_group);
            $videos_historique = $em->getRepository('MongoboxJukeboxBundle:VideoGroup')->findLast(5, $id_group);
            $video_en_cours = $em->getRepository('MongoboxJukeboxBundle:Playlist')->findOneBy(
                array('group' => $id_group, 'current' => 1)
            );
            if (is_object($video_en_cours)) {
                $somme = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($video_en_cours->getId());
            } else {
                $somme = 0;
            }
            $somme_pl = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeAllVotes();

            return array
            (
                //'total_video' => $total_video,
                'playlist'          => $playlist,
                'videos_historique' => $videos_historique,
                'video_en_cours'    => $video_en_cours,
                'date_actuelle'     => new \Datetime(),
                'somme'             => $somme,
                'somme_pl'          => $somme_pl
            );
        } //Si l'utilisateur n'a pas de groupe, on propose une liste de group publics
        else {
            return $this->redirect($this->generateUrl('group_index'));
        }
    }

    /**
     * @Template()
     * @Route( "/jukebox", name="jukebox")
     */
    public function jukeboxAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $group = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));

        $video_en_cours = $em->getRepository('MongoboxJukeboxBundle:Playlist')->findOneBy(
            array('group' => $group->getId(), 'current' => 1)
        );
        if (count($video_en_cours) > 0) {
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
        if (is_object($video_en_cours)) {
            $em->remove($video_en_cours);
        }

        $total_video = count($em->getRepository('MongoboxJukeboxBundle:Videos')->findAll());
        $playlist = $em->getRepository('MongoboxJukeboxBundle:Playlist')->next(10, $group);

        $em->flush();

        return array
        (
            'video_group' => $video_group,
            'total_video' => $total_video,
            'playlist'    => $playlist
        );
    }

    /**
     * @Template()
     * @Route( "/next", name="next_video")
     */
    public function nextAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('MongoboxJukeboxBundle:Playlist')->generate();

        return new Response();
    }

    /**
     * @Template()
     * @Route( "/vote/{id}/{sens}", name="vote")
     * @ParamConverter("playlist", class="MongoboxJukeboxBundle:Playlist")
     */
    public function voteAction(Request $request, $playlist, $sens)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        // Wipe de son ancien vote
        $old_vote = $em->getRepository('MongoboxJukeboxBundle:Vote')
            ->findOneBy(
                array(
                    'user'     => $user,
                    'playlist' => $playlist,
                )
            );
        if (!is_null($old_vote)) {
            $em->remove($old_vote);
            $em->flush();
        }

        if ($sens != 0) {
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
     *
     * @Route( "/ajax_is_vote_next", name="ajax_is_vote_next")
     */
    public function ajaxIsVoteNextAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /*$video_en_cours = $em->getRepository('MongoboxJukeboxBundle:VideoCurrent')->findAll();*/
        $somme = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($video_en_cours[0]->getId());
        if ($somme <= -2) {
            $is_next = true;
        } else {
            $is_next = false;
        }

        return new Response(json_encode(array('next' => $is_next)));
    }

    /**
     * Display block current video
     *
     * @Template("MongoboxCoreBundle:Wall/Blocs:videoEnCours.html.twig")
     * @Route( "/video_en_cours", name="video_en_cours")
     */
    public function videoEnCoursAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        if (!is_null($session->get('id_group'))) {
            $video_en_cours = $em->getRepository('MongoboxJukeboxBundle:Playlist')->findOneBy(
                array('group' => $session->get('id_group'), 'current' => 1)
            );
            if (is_object($video_en_cours)) {
                $somme = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeVotes($video_en_cours->getId());
            } else {
                $somme = 0;
            }
        } else {
            $video_en_cours = null;
            $somme = 0;
        }

        if (!is_null($request->query->get('json'))) {
            $render = $this->render(
                'MongoboxCoreBundle:Wall/Blocs:videoEnCours.html.twig',
                array(
                    'video_en_cours' => $video_en_cours,
                    'date_actuelle'  => new \Datetime(),
                    'somme'          => $somme
                )
            );
            $json = array('render' => $render->getContent());

            return new Response(json_encode($json));
        } else {
            return array(
                'video_en_cours' => $video_en_cours,
                'date_actuelle'  => new \Datetime(),
                'somme'          => $somme
            );
        }
    }

    /**
     * Display block statistics
     *
     * @Template("MongoboxCoreBundle:Wall/Blocs:statistiques.html.twig")
     * @Route( "/statistiques", name="statistiques")
     */
    public function statistiquesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $session = $request->getSession();
        $user->getGroups();

        //Si l'utilisateur a au moins un groupe
        if (count($user->getGroups()) > 0) {
            $playlist = $em->getRepository('MongoboxJukeboxBundle:Playlist')->next(10, $session->get('id_group'));
            $videos_historique =
                $em->getRepository('MongoboxJukeboxBundle:VideoGroup')->findLast(5, $session->get('id_group'));
            $video_en_cours = $em->getRepository('MongoboxJukeboxBundle:Playlist')->findOneBy(
                array('group' => $session->get('id_group'), 'current' => 1)
            );
            $somme_pl = $em->getRepository('MongoboxJukeboxBundle:Vote')->sommeAllVotes();

            if (!is_null($request->query->get('json'))) {
                $render = $this->render(
                    'MongoboxCoreBundle:Wall/Blocs:statistiques.html.twig',
                    array(
                        'video_en_cours'    => $video_en_cours,
                        //'total_video' => $total_video,
                        'playlist'          => $playlist,
                        'videos_historique' => $videos_historique,
                        'somme_pl'          => $somme_pl
                    )
                );
                $json = array('render' => $render->getContent());

                return new Response(json_encode($json));
            } else {
                return array(
                    'video_en_cours'    => $video_en_cours,
                    //'total_video' => $total_video,
                    'playlist'          => $playlist,
                    'videos_historique' => $videos_historique,
                    'somme_pl'          => $somme_pl
                );
            }
        }

        return new Response();
    }

    /**
     * Action for Flux RSS Blocks
     *
     * @Template("MongoboxCoreBundle:Wall/Blocs:fluxRss.html.twig")
     * @Route( "/flux_rss", name="flux_rss")
     */
    public function fluxRssAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $rssFeeds = $em->getRepository('MongoboxCoreBundle:Feed')->findAll();

        return array
        (
            'flux_rss' => $rssFeeds
        );
    }
}
