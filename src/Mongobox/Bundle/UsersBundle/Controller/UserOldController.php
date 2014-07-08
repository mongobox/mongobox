<?php

namespace Mongobox\Bundle\UsersBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mongobox\Bundle\UsersBundle\Entity\UserOld;
use Mongobox\Bundle\UsersBundle\Form\UserOldType;

/**
 * UserOld controller.
 *
 * @Route("/user/profil")
 */
class UserOldController extends Controller
{

    /**
     * Creates a new UserOld entity.
     *
     * @Route("/confirm", name="userold_create")
     * @Method("POST")
     * @Template("MongoboxUsersBundle:UserOld:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $baseEntity = $em->getRepository('MongoboxUsersBundle:UserOld')->findOneByLastId($user->getId());
        if (!empty($baseEntity)) {
            $entity = $baseEntity;
        } else {
            $entity  = new UserOld();
        }

        $form = $this->createForm(new UserOldType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {

            $entity->setLastId($user->getId());
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('wall_index', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new UserOld entity.
     *
     * @Route("/confirm", name="userold_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $baseEntity = $em->getRepository('MongoboxUsersBundle:UserOld')->findOneByLastId($user->getId());
        if (!empty($baseEntity)) {
            $entity = $baseEntity;
        } else {
            $entity  = new UserOld();
            $entity->setEmail($user->getEmail());
        }
        $form   = $this->createForm(new UserOldType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
}
