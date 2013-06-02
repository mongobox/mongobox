<?php

namespace Mongobox\Bundle\UsersBundle\Controller;

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

		$nombre_favoris = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getBookmarkNumber($user);
		$nombre_listes = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getListsNumber($user);

		$lists = $manager->getRepository('MongoboxUsersBundle:ListeFavoris')->getListsAndVideos($user);
		$favoris = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getAllUserFavoris($user);

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
	 */
	public function importAction()
	{
		$request = $this->getRequest();
		$manager = $this->getDoctrine()->getManager();
		$user = $this->getUser();

		$nombre_favoris = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getBookmarkNumber($user);
		$nombre_listes = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getListsNumber($user);

		if( $request->isMethod('POST') )
		{
			$videosRepository = $manager->getRepository('MongoboxJukeboxBundle:Videos');
			$videosGroupeRepository = $manager->getRepository('MongoboxJukeboxBundle:VideoGroup');
			$groupRepository = $manager->getRepository('MongoboxGroupBundle:Group');
			$infos = array();
			$videos = $request->request->get('videos');
            $group = $request->request->get('group');
			echo '<pre>';
			var_dump($group);
			foreach($videos as $id_video)
			{
				//$present = $
				var_dump($id_video);
			}
			exit;
		}

		return array(
			'nombre_favoris' => $nombre_favoris,
			'nombre_listes' => $nombre_listes
		);
	}
}
