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
	/**
	 * Fonction pour ajouter une vidéo à la liste des vidéos favorites
	 * @Route("/ajax/favorite/new/{id_video}", name="ajax_new_video_favorite")
	 */
	public function ajaxVideoAddToFavorite($id_video)
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
					->setVideo($em->getRepository('MongoboxJukeboxBundle:Videos')->find($id_video))
					->setDateFavoris(new \DateTime)
			;

			$em->persist($newFavoris);
			$em->flush();

			$result['add'] = true;
			$result['already'] = false;
		}

		return new Response(json_encode($result));
	}
}

?>
