<?php

namespace Mongobox\Bundle\UsersBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Mongobox\Bundle\UsersBundle\Form\UserEditType;
use Mongobox\Bundle\UsersBundle\Form\UserEditPasswordType;

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

	/**
	 * Fonction pour l'édition de l'utilisateur courant
	 * @Template()
	 * @Route("/profil/edit", name="user_edit")
	 * @Method({ "GET", "POST" }) 
	 */
	public function editAction(Request $request, $id_user = null)
	{		
		$em = $this->getDoctrine()->getEntityManager();

		$request = $this->container->get('request');
		$user = $this->container->get('security.context')->getToken()->getUser();

		$old_user = clone $user;
		
		//Formulaire de modification de l'utilisateur
		$form = $this->createForm(new UserEditType(), $user);
		//Formulaire de modification du mot de passe si email non collectif
		$form_password = $this->createForm(new UserEditPasswordType(), $user);
		
		if('POST' === $request->getMethod())
		{
			//Validation pour l'utilisateur
			if($request->request->has('utilisateur_edition'))
			{
				$form->bindRequest($request);
				if($form->isValid())
				{
					$factory = $this->get('security.encoder_factory');

					$user->setDateUpdate(new \DateTime());
					$em->flush();
					$this->get('session')->setFlash('success', 'Profil modifié avec succès');
				}
			}
			//Validation pour le mot de passe
			elseif($request->request->has('utilisateur_edition_mot_de_passe'))
			{
				$form_password->bindRequest($request);
				if($form_password->isValid())
				{
					$factory = $this->get('security.encoder_factory');
					$encoder = $factory->getEncoder($user);
					//On vÃ©rifie que l'ancien mot de passe et bien conforme
					if($encoder->encodePassword($form_password->get('old_password')->getData(), $user->getSalt()) == $user->getPassword())
					{
						$user->setPassword($form_password->get('new_password')->getData());
						$user->encodePassword($encoder);
						$user->setDateUpdate(new \DateTime());
						$em->flush();
						$this->get('session')->setFlash('success', 'Votre mot de passe a été modifié avec succès');
					}
				}
			}
		}

		return array(
			'form' => $form->createView(),
			'form_password' => $form_password->createView(),
			'user' => $user
		);
	}
}