<?php

namespace Mongobox\Bundle\UsersBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Mongobox\Bundle\UsersBundle\Entity\UserFavoris;


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

		return new Response(json_encode($result));
	}

	/**
	 * Fonction pour voir les favoris de l'utilisateur
	 * @Route("/profil/favoris/{page}", name="user_voir_favoris", requirements={"page" = "\d+"}, defaults={"page" = 0})
	 * @Template()
	 */
	public function voirFavorisAction($page)
	{
		$manager = $this->getDoctrine()->getManager();
		$user = $this->getUser();
		$nombre_favoris = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getNombreFavoris($user);
		$nombre_listes = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getNombreListes($user);
		$videos_user = $manager->getRepository('MongoboxUsersBundle:UserFavoris')->getUniqueFavorisUser($user, $page, self::_limitation_favoris);

		$nextPage = false;
		if(count($videos_user) > self::_limitation_favoris)
		{
			unset($videos_user[self::_limitation_favoris-1]);
			$nextPage = true;
		}

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

		return array(
			'favoris' => $videos_user,
			'nombre_favoris' => $nombre_favoris,
			'nombre_listes' => $nombre_listes,
			'next_page' => $nextPage
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

		return new Response(json_encode($retour));
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

			$retour = array(
				"success" => true,
				"message" => "Vidéo supprimée de la liste avec succès"
			);
		} else
		{
			$retour = array(
				"success" => false,
				"message" => "Echec lors de la suppression de la vidéo"
			);
		}

		return new Response(json_encode($retour));
	}

	/**
	 * Fonction pour voir les listes de favoris de l'utilisateur
	 * @Route("/profil/listes", name="user_voir_listes")
	 * @Template()
	 */
	public function voirListeFavorisAction()
	{
		return array();
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

		return new Response(json_encode($json));
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

		return new Response(json_encode(array(
			"message" => $message,
			"result" => $result,
			"html" => $html
		)));
	}
}

?>
