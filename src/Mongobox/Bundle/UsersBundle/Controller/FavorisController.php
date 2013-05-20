<?php

namespace Mongobox\Bundle\UsersBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Mongobox\Bundle\UsersBundle\Entity\ListeFavoris;
use Mongobox\Bundle\UsersBundle\Entity\UserFavoris;


class FavorisController extends Controller
{
	const _limitation_favoris = 5;
	const _limitation_listes = 5;

	/**
	 * Fonction pour ajouter une vidéo à la liste des vidéos favorites
	 * @Route("/ajax/favorite/new/{id_video}", name="ajax_new_video_favorite")
	 */
	public function ajaxVideoAddToFavoriteAction($id_video)
	{
		$user = $this->getUser();
		$em = $this->getDoctrine()->getManager();
		$isAlreadyFavorite = $em->getRepository('MongoboxUsersBundle:UserFavoris')->checkUserFavorite($id_video, $user);

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
		$videos_user = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getUniqueFavorisUser($user, $page, self::_limitation_favoris);

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
			$array_date_first_ajout = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getDateAddToFavorite($user, $video['id']);
			$video = array_merge($video, $array_date_first_ajout);
			$video['listes'] = $manager->getRepository('MongoboxUsersBundle:ListeFavoris')->getListesUserForOneVideo($user, $video['id']);
			foreach($video['listes'] as &$liste)
			{
				$array_date_ajout = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getDateAddToList($user, $video['id'], $liste['id']);
				$liste = array_merge($liste, $array_date_ajout);
			}
		}

		// Si c'est une requête ajax, on renvoie le tableau json avec le html et la page
		if( $request->isXmlHttpRequest() )
		{
			$html = $this->render('MongoboxUsersBundle:Favoris/Listes:listeFavoris.html.twig', array('favoris' => $videos_user))->getContent();
			return new JsonResponse(array(
				"html" => $html,
				"nextPage" => $nextPage,
				"page" => $page+1
			));
		} else
		{
			$nombre_favoris = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getBookmarkNumber($user);
			$nombre_listes = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getListsNumber($user);
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

		$is_removed = $em->getRepository("MongoboxUsersBundle:UserFavoris")->removeVideoFromBookmark($user, $id_video);

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

		$fav = $em->getRepository("MongoboxUsersBundle:UserFavoris")->findOneBy(array(
			"user" => $user,
			"video" => $em->find("MongoboxJukeboxBundle:Videos", $id_video),
			"liste" => $em->find("MongoboxUsersBundle:ListeFavoris", $id_liste)
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
	 * Fonction pour voir les listes de favoris de l'utilisateur
	 * @Route("/profil/listes", name="user_voir_listes")
	 * @Template()
	 */
	public function voirListeFavorisAction()
	{
		$manager = $this->getDoctrine()->getManager();
		$user = $this->getUser();

		$nombre_favoris = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getBookmarkNumber($user);
		$nombre_listes = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getListsNumber($user);

		$listes = $user->getListesFavoris();

		return array(
			'nombre_favoris' => $nombre_favoris,
			'nombre_listes' => $nombre_listes,
			'listes' => $listes
		);
	}

	/**
	 * Fonction permettant de récupérer via JSON la liste des listes de favoris de l'utilisateur
	 * @Route("/ajax_list_search", name="ajax_list_search")
	 */
	public function ajaxListSearchAction(Request $request)
	{
		$value = $request->get('term');
		$em = $this->getDoctrine()->getManager();

		$lists = $em->getRepository('MongoboxUsersBundle:ListeFavoris')->findList($value);

		$json = array();
		foreach ($lists as $list)
		{
			$json[] = array(
				'label' => $list->getName(),
				'value' => $list->getId()
			);
		}

		return new JsonResponse($json);
	}

	/**
	 * Fonction pour ajouter un favoris à une liste
	 * @Route("/ajax/favoris/{id_video}/add/liste", name="ajax_liste_favoris_add", requirements={"id_video" = "\d+"})
	 */
	public function addListToBookmarkAction($id_video)
	{
		$user = $this->getUser();
		$em = $this->getDoctrine()->getManager();
		$id_liste = $this->getRequest()->request->get('id_liste');
		$liste = $em->getRepository('MongoboxUsersBundle:ListeFavoris')->find($id_liste);
		$video = $em->find('MongoboxJukeboxBundle:Videos', $id_video);

		$alreadyExist = $em->getRepository('MongoboxUsersBundle:UserFavoris')->findOneBy(array(
			'user' => $user,
			'video' => $video,
			'liste' => $liste
		));

		$date = new \DateTime;
		$message = 'La vidéo existe déjà dans la liste "'.$liste->getName().'"';
		$result = false;

		if( is_null($alreadyExist) )
		{
			$new_fav_list = new UserFavoris();
			$new_fav_list
				->setUser($user)
				->setListe($liste)
				->setVideo($video)
				->setDateFavoris($date)
			;
			$em->persist($new_fav_list);
			$em->flush();

			$message = 'Vidéo ajoutée avec succès dans la liste "'.$liste->getName().'"';
			$result = true;
		}

		$html = '';
		if( $result )
		{
			$html = $this->renderView('MongoboxUsersBundle:Favoris/Listes:uneListeFavoris.html.twig', array('liste' => $liste, 'ajax' => true, 'date' => $date ,'video' => $video));
		}

		return new JsonResponse(array(
			"message" => $message,
			"result" => $result,
			"html" => $html
		));
	}

	/**
	 * Fonction pour créer une nouvelle liste en ajax
	 * @Route("/ajax/create/list", name="add_new_list")
	 */
	public function createNewListAction()
	{
		try
		{
			$em = $this->getDoctrine()->getManager();
			$request = $this->getRequest();
			$user = $this->getUser();
			$currentRoute = $request->request->get('routeName');
			$listName = $request->request->get('listName');

			$newList = new ListeFavoris();
			$newList
				->setUser($user)
				->setName($listName)
				->setDate_creation(new \DateTime)
			;

			$em->persist($newList);
			$em->flush();

			$json = array(
				'success' => true,
				'listName' => $listName,
				'currentRoute' => false
			);

			$json['listNumber'] = $em->getRepository('MongoboxUsersBundle:UserFavoris')->getListsNumber($user);
			if( $currentRoute === 'user_voir_listes' )
			{
				$json['currentRoute'] = true;
				$json['limitation'] = self::_limitation_listes;
				$json['html'] = $this->renderView('MongoboxUsersBundle:Favoris/Listes:uneListe.html.twig', array('liste' => $newList));
			}
		} catch( \Exception $e)
		{
			$json = array('success' => false);
		}

		return new JsonResponse($json);
	}

	/**
	 * Fonction pour supprimer une liste de favoris
	 * @Route("/ajax/list/remove/{id_list}", name="remove_list_action", requirements={"id" = "\d+"})
	 */
	public function removeListAction($id_list)
	{
		$em = $this->getDoctrine()->getManager();
		$user = $this->getUser();
		$json = array();
		try
		{
			$json['success'] = true;
			$json['message'] = "Veuillez séléctionner une liste pour afficher les vidéos";
			$listToRemove = $em->getRepository('MongoboxUsersBundle:ListeFavoris')->find($id_list);
			$em->getRepository('MongoboxUsersBundle:UserFavoris')->removeBookmarkFromList($listToRemove, $user);
			$em->remove($listToRemove);
			$em->flush();
			$json['listNumber'] = $em->getRepository('MongoboxUsersBundle:UserFavoris')->getListsNumber($user);

		} catch( \Exception $e )
		{
			$json['success'] = false;
		}

		return new JsonResponse($json);
	}

	/**
	 * Fonction pour récupérer les détails d'une liste
	 * @Route("/ajax/list/details/{id_list}", name="details_list_action", requirements={"id" = "\d+"})
	 */
	public function getListDetailsAction($id_list)
	{
		$user = $this->getUser();
		$manager = $this->getDoctrine()->getManager();
		$json = array();
		/*try
		{

		} catch( \Exception $e )
		{
			$json['success'] = false;
			$json['error'] = 'Le chargement de la liste a échoué';
		}*/
		$list = $manager->getRepository('MongoboxUsersBundle:ListeFavoris')->find($id_list);
		$videos = $manager->getRepository('MongoboxUsersBundle:ListeFavoris')->getBookmarkFromList($list, $user);
		$json['success'] = true;
		$json['html'] = $this->renderView('MongoboxUsersBundle:Favoris/Listes:listeDetails.html.twig', array('list' => $list, 'bookmarks' => $videos));

		/*echo '<pre>';
		\Doctrine\Common\Util\Debug::dump($videos);
		exit;*/
		return new JsonResponse($json);
	}
}

?>
