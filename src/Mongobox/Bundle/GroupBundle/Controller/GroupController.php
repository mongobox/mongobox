<?php

namespace Mongobox\Bundle\GroupBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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
}