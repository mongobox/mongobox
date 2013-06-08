<?php

namespace Mongobox\Bundle\BookmarkBundle\Controller;

use Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Doctrine\Common\Util\Debug;

class ImportController extends Controller
{
    /**
     * Fonction d'affichage de l'import
     * @Route("/favoris/import", name="import_favoris_index_action")
     * @Template()
     */
    public function indexAction()
    {
        $manager = $this->getDoctrine()->getManager();
        $user = $this->getUser();

		$nombre_favoris = $manager->getRepository('MongoboxBookmarkBundle:UserFavoris')->getBookmarkNumber($user);
		$nombre_listes = $manager->getRepository('MongoboxBookmarkBundle:UserFavoris')->getListsNumber($user);

		$lists = $manager->getRepository('MongoboxBookmarkBundle:ListeFavoris')->getListsAndVideos($user);
		$favoris = $manager->getRepository('MongoboxBookmarkBundle:UserFavoris')->getAllUserFavoris($user);

        return array(
            'nombre_favoris' => $nombre_favoris,
            'nombre_listes' => $nombre_listes,
            'lists' => $lists,
            'favoris' => $favoris
        );
    }

    /**
     * Fonction pour traiter l'import
     * @Route("/import/traitment", name="import_action")
     * @Template()
     */
    public function importAction()
    {
        $request = $this->getRequest();
        $manager = $this->getDoctrine()->getManager();
        $user = $this->getUser();

		$nombre_favoris = $manager->getRepository('MongoboxBookmarkBundle:UserFavoris')->getBookmarkNumber($user);
		$nombre_listes = $manager->getRepository('MongoboxBookmarkBundle:UserFavoris')->getListsNumber($user);

        $infos = array();
        $nbVideoImport = 0;

		if( $request->isMethod('POST') )
		{
			$type = $request->request->get('type-import-choice');

			$videos = ($type === "video") ? $request->request->get('videos') : $request->request->get('videos_list');
			$id_group = $request->request->get('group');

            $videosRepository = $manager->getRepository('MongoboxJukeboxBundle:Videos');
            $videosGroupeRepository = $manager->getRepository('MongoboxJukeboxBundle:VideoGroup');

            $group = $manager->find('MongoboxGroupBundle:Group', $id_group);

            foreach ($videos as $id_video) {
                $video = $videosRepository->find($id_video);
                $present = $videosGroupeRepository->findOneBy(array(
                    "video" => $video,
                    "group" => $group
                ));

                if (!$present) {
                    if ( !array_key_exists($id_group, $infos) ) {
                        $newVideoGroup = new VideoGroup();
                        $newVideoGroup
                            ->setVideo($video)
                            ->setGroup($group)
                            ->setUser($user)
                            ->setDiffusion(0)
                            ->setVolume(50)
                            ->setVotes(0)
                        ;
                        $manager->persist($newVideoGroup);

                        $infos[$id_video] = array(
                            "title" => $video->getTitle(),
                            "message" => "Vidéo importée avec succès",
                            "type" => "success"
                        );

                        $nbVideoImport++;
                    }
                } else {
                    if ( !array_key_exists($id_group, $infos) ) {
                        $infos[$id_video] = array(
                            "title" => $video->getTitle(),
                            "message" => "La vidéo est déjà présente dans le groupe",
                            "type" => "notice"
                        );
                    }
                }
            }

            $manager->flush();
        }

        return array(
            'nombre_favoris' => $nombre_favoris,
            'nombre_listes' => $nombre_listes,
            'infos' => $infos,
            'nb_import' => $nbVideoImport
        );
    }

	/**
	 * Fonction pour charger en ajax le nom de vidéos
	 * @Route("/import/group/{group_id}/load/videos", name="ajax_load_group_video")
	 */
	public function ajaxLoadGroupVideo($group_id)
	{
		$videosName = $this->getDoctrine()->getManager()->getRepository('MongoboxJukeboxBundle:VideoGroup')->getVideoNameInGroup($group_id);
		return $this->render('MongoboxBookmarkBundle:Import/Videos:listeVideos.html.twig', array('videos' => $videosName));
	}
}
