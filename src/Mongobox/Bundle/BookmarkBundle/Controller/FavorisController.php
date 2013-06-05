<?php

namespace Mongobox\Bundle\BookmarkBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Mongobox\Bundle\BookmarkBundle\Entity\ListeFavoris;
use Mongobox\Bundle\BookmarkBundle\Entity\UserFavoris;


class FavorisController extends Controller
{
	const _limitation_favoris = 5;

	/**
	 * Fonction pour ajouter une vidéo à la liste des vidéos favorites
	 * @Route("/ajax/favorite/new/{id_video}", name="ajax_new_video_favorite")
	 */
	public function ajaxVideoAddToFavoriteAction($id_video)
	{
		$user = $this->getUser();
		$em = $this->getDoctrine()->getManager();
		$isAlreadyFavorite = $em->getRepository('MongoboxBookmarkBundle:UserFavoris')->checkUserFavorite($id_video, $user);

		$result = array(
			'add' =>  false,
			'already' => true
		);

		if(!$isAlreadyFavorite)
		{
			$newFavoris = new UserFavoris();
			$newFavoris
					->setUser($user)
					->setVideo($em->find('MongoboxJukeboxBundle:Videos', $id_video))
					->setDateFavoris(new \DateTime)
			;

			$em->persist($newFavoris);
			$em->flush();

			$result['add'] = true;
			$result['already'] = false;
		}

		return new JsonResponse($result);
	}

	/**
	 * Fonction pour voir les favoris de l'utilisateur
	 * @Route("/profil/favoris/{page}", name="user_voir_favoris", requirements={"page" = "\d+"}, defaults={"page" = 1})
	 * @Route("/profil/favoris/plus", name="user_voir_plus_favoris", defaults={"page" = 2})
	 * @Template()
	 */
	public function voirFavorisAction($page)
	{
		$request = $this->getRequest();
		$manager = $this->getDoctrine()->getManager();
		$user = $this->getUser();

		if( $request->isXmlHttpRequest() )
			$page = $request->request->get('page');

		// On récupère les favoris
		$videos_user = $manager->getRepository('MongoboxBookmarkBundle:UserFavoris')->getUniqueFavorisUser($user, $page, self::_limitation_favoris);

		// On définit s'il y a une page après celle courante
		$nextPage = false;
		if(count($videos_user) > self::_limitation_favoris)
		{
			unset($videos_user[self::_limitation_favoris]);
			$nextPage = true;
		}

		// On traite les vidéos pour avoir les infos directement dedans
		foreach($videos_user as &$video)
		{
			$array_date_first_ajout = $manager->getRepository('MongoboxBookmarkBundle:UserFavoris')->getDateAddToFavorite($user, $video['id']);
			$video = array_merge($video, $array_date_first_ajout);
			$video['listes'] = $manager->getRepository('MongoboxBookmarkBundle:ListeFavoris')->getListesUserForOneVideo($user, $video['id']);
			foreach($video['listes'] as &$liste)
			{
				$array_date_ajout = $manager->getRepository('MongoboxBookmarkBundle:UserFavoris')->getDateAddToList($user, $video['id'], $liste['id']);
				$liste = array_merge($liste, $array_date_ajout);
			}
		}

		// Si c'est une requête ajax, on renvoie le tableau json avec le html et la page
		if( $request->isXmlHttpRequest() )
		{
			$html = $this->render('MongoboxBookmarkBundle:Favoris/Listes:listeFavoris.html.twig', array('favoris' => $videos_user))->getContent();
			return new JsonResponse(array(
				"html" => $html,
				"nextPage" => $nextPage,
				"page" => $page+1
			));
		} else
		{
			$nombre_favoris = $manager->getRepository('MongoboxBookmarkBundle:UserFavoris')->getBookmarkNumber($user);
			$nombre_listes = $manager->getRepository('MongoboxBookmarkBundle:UserFavoris')->getListsNumber($user);
		}

		return array(
			'favoris' => $videos_user,
			'nombre_favoris' => $nombre_favoris,
			'nombre_listes' => $nombre_listes,
			'next_page' => $nextPage,
			'page' => $page+1
		);
	}

	/**
	 * Fonction pour supprimer une vidéo des favoris
	 * @Route("/ajax/favoris/{id_video}/remove", name="remove_video_bookmark", requirements={"id_video" = "\d+"})
	 */
	public function removeVideoFromBookmarkAction($id_video)
	{
		$user = $this->getUser();
		$em = $this->getDoctrine()->getManager();

		$is_removed = $em->getRepository("MongoboxBookmarkBundle:UserFavoris")->removeVideoFromBookmark($user, $id_video);

		$retour = array(
			'success' => $is_removed,
			'message' => ($is_removed) ? "Vidéo supprimée avec succès de vos favoris": "Echec lors de la suppression",
			"fav" => $id_video
		);

		return new JsonResponse($retour);
	}

	/**
	 * Fonction pour supprimer une vidéo dans une liste
	 * @Route("/ajax/favoris/{id_video}/remove/liste/{id_liste}", name="remove_video_liste", requirements={"id_video" = "\d+", "id_liste" = "\d+"})
	 */
	public function removeVideoFromListAction($id_video, $id_liste)
	{
		$user = $this->getUser();
		$em = $this->getDoctrine()->getManager();

		$fav = $em->getRepository("MongoboxBookmarkBundle:UserFavoris")->findOneBy(array(
			"user" => $user,
			"video" => $em->find("MongoboxJukeboxBundle:Videos", $id_video),
			"liste" => $em->find("MongoboxBookmarkBundle:ListeFavoris", $id_liste)
		));

		if( is_object($fav) )
		{
			$em->remove($fav);
			$em->flush();

			$json = array(
				"success" => true,
				"message" => "Vidéo supprimée de la liste avec succès"
			);
		} else
		{
			$json = array(
				"success" => false,
				"message" => "Echec lors de la suppression de la vidéo"
			);
		}

		return new JsonResponse($json);
	}

	/**
	 * Fonction permettant de récupérer via JSON la liste des favoris pour l'utilisateur
	 * @Route("/ajax_bookmark_search", name="ajax_bookmark_search")
	 */
	public function ajaxBookmarkSearchAction()
	{
		$value = $this->getRequest()->get('term');
		$em = $this->getDoctrine()->getManager();
		$user = $this->getUser();

		$bookmarks = $em->getRepository('MongoboxBookmarkBundle:UserFavoris')->findBookmark($user, $value);

		$json = array();
		foreach ($bookmarks as $bookmark)
		{
			$json[] = array(
				'label' => $bookmark->getTitle(),
				'value' => $bookmark->getId()
			);
		}

		return new JsonResponse($json);
	}
}

?>
