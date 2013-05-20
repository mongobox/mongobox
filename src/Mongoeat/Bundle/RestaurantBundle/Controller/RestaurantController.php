<?php

namespace Mongoeat\Bundle\RestaurantBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mongoeat\Bundle\RestaurantBundle\Entity\Restaurant;
use Mongoeat\Bundle\RestaurantBundle\Form\RestaurantType;
use Symfony\Component\Process\Process;

/**
 * Restaurant controller.
 *
 * @Route("/mongoeat/restaurant")
 */
class RestaurantController extends Controller
{
    /**
     * Lists all Restaurant entities.
     *
     * @Route("/", name="restaurant")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('MongoeatRestaurantBundle:Restaurant')->findSortVotes();

        return array(
            'entities' => $entities->getQuery()->getResult(),

        );
    }
    /**
     * Lists all Restaurant entities.
     *
     * @Route("/find", name="restaurant_find")
     * @Method("GET")
     */
    public function findAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $group = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));
        $p = new Process('php ../app/console mongoeat:restaurant:find '.$group->getCity());
        $p->run();
        if($p->isSuccessful())

            return $this->redirect($this->generateUrl('restaurant'));
        else
            var_dump($p->getErrorOutput());
        exit;
    }

    /**
     * Creates a new Restaurant entity.
     *
     * @Route("/", name="restaurant_create")
     * @Method("POST")
     * @Template("MongoeatRestaurantBundle:Restaurant:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Restaurant();
        $form = $this->createForm(new RestaurantType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('restaurant_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Restaurant entity.
     *
     * @Route("/new", name="restaurant_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Restaurant();
        $form   = $this->createForm(new RestaurantType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Restaurant entity.
     *
     * @Route("/{id}", name="restaurant_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MongoeatRestaurantBundle:Restaurant')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Restaurant entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Finds and displays a Restaurant entity.
     *
     * @Route("/json/{id}", name="restaurant_show_ajax")
     * @Method("GET")
     */
    public function showAjaxAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MongoeatRestaurantBundle:Restaurant')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Restaurant entity.');
        }

        return new JsonResponse($entity->toArray());
    }

    /**
     * Displays a form to edit an existing Restaurant entity.
     *
     * @Route("/{id}/edit", name="restaurant_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MongoeatRestaurantBundle:Restaurant')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Restaurant entity.');
        }

        $editForm = $this->createForm(new RestaurantType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Restaurant entity.
     *
     * @Route("/{id}/update", name="restaurant_update")
     * @Method("POST")
     * @Template("MongoeatRestaurantBundle:Restaurant:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MongoeatRestaurantBundle:Restaurant')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Restaurant entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new RestaurantType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('restaurant_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Restaurant entity.
     *
     * @Route("/{id}/delete", name="restaurant_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MongoeatRestaurantBundle:Restaurant')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Restaurant entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('restaurant'));
    }

    /**
     * Creates a form to delete a Restaurant entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
