<?php

namespace Mongobox\Bundle\GroupBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Cookie;

use Mongobox\Bundle\GroupBundle\Entity\Group;

/**
 * @Route( "/group")
 * 
 */
class GroupController extends Controller
{
    /**
     * @Template()
     * @Route( "/inscription/{id}", name="group_inscription")
     * @ParamConverter("group", class="MongoboxGroupBundle:Group")
     */
    public function groupInscriptionAction(Request $request, Group $group)
    {
        $em = $this->getDoctrine()->getEntityManager();
		$user = $this->get('security.context')->getToken()->getUser();

		$group->getUsers()->add($user);

		$em->flush();

		$this->get('session')->setFlash('success', 'Inscription au groupe "'.$group->getTitle().'" réussie.');

		return $this->redirect($this->generateUrl('homepage'));
	}

	/**
	 * @Route("/change_group/{id_group}", name="change_group")
	 * 
	 * @author pbo
	 * Change la groupe courante (via la liste de selection)
	 */
	public function changeGroupAction(Request $request, $id_group)
	{
		$em = $this->getDoctrine()->getEntityManager();

		$session = $request->getSession();
		$session->set('id_group', $id_group);

		//On met l'id du groupe en cookie
		$response = new Response(); 
		$response->headers->setCookie(new Cookie('id_group', $id_group));
		$response->send();

		return $this->redirect($this->generateUrl('homepage'));
	}
}