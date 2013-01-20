<?php

namespace Mongobox\Bundle\UsersBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route( "/user")
 * 
 */
class UserController extends Controller
{
	/**
	 * Fonction permettant de récupérer via JSON la liste des utilisateurs
	 * @Route("/ajax_user_search", name="ajax_user_search")
	 */
	public function ajaxUserSearchAction(Request $request)
	{
		$value = $request->get('term');

		$em = $this->getDoctrine()->getEntityManager();
		
		$users = $em->getRepository('MongoboxUsersBundle:User')->findBy(array('login' => $value));

		$json = array();
		foreach ($users as $user)
		{
			$json[] = array(
					'label' => $user->getLogin(),
					'value' => $user->getId()
			);
		}

		//On renvoi la chaîne si l'organisme n'existe pas
		if(count($json) == 0)
		{
			$donnees = array('label' => $value, 'value' => 0);
			return new Response(json_encode($donnees));
		}

		return new Response(json_encode($json));
	}
}