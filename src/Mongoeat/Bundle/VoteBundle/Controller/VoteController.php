<?php

namespace Mongoeat\Bundle\VoteBundle\Controller;

use Mongoeat\Bundle\VoteBundle\Entity\Decision;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mongoeat\Bundle\VoteBundle\Entity\Vote;
use Mongoeat\Bundle\VoteBundle\Form\VoteType;

/**
 * Vote controller.
 *
 * @Route("/mongoeat/vote")
 */
class VoteController extends Controller
{

    /**
     * Creates a new Vote entity.
     *
     * @Route("/{id}", name="vote_create")
     * @Method("POST")
     * @Template("MongoeatVoteBundle:Vote:new.html.twig")
     */
    public function createAction(Request $request,$id)
    {
        $entity  = new Vote();
        $em = $this->getDoctrine()->getManager();
        $decision = $em->getRepository('MongoeatVoteBundle:Decision')->findOneById($id);

        if (!$decision) {
            throw $this->createNotFoundException('Unable to find Decision entity.');
        }

        $form = $this->createForm(new VoteType($decision->getGroup()->getCity()), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $user = $this->get('security.context')->getToken()->getUser();
            $vote = $em->getRepository('MongoeatVoteBundle:Vote')->findOneBy(array('user'=>$user->getId(),'decision'=>$id));

            if (!empty($vote)) {
                return $this->redirect($this->generateUrl('vote_show',array('id'=>$vote->getId())));
            }

            $now = new \DateTime();
            if ($decision->getDate()->format('Ymd') != $now->format('Ymd')) {
                return $this->redirect($this->generateUrl('decision'));
            }

            $entity->setDecision($decision);
            $entity->setUser($user);

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('vote_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Vote entity.
     *
     * @Route("/{id}/new", name="vote_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $vote = $em->getRepository('MongoeatVoteBundle:Vote')->findOneBy(array('user'=>$user->getId(),'decision'=>$id));
        if (!empty($vote)) {
            return $this->redirect($this->generateUrl('vote_show',array('id'=>$vote->getId())));
        }
        $entity = new Vote();

        $decision = $em->getRepository('MongoeatVoteBundle:Decision')->findOneById($id);

        if (!$decision) {
            throw $this->createNotFoundException('Unable to find Decision entity.');
        }
        $now = new \DateTime();
        if ($decision->getDate()->format('Ymd') != $now->format('Ymd')) {
            return $this->redirect($this->generateUrl('decision'));
        }
        $entity->setDecision($decision);
        $entity->setUser($user);
        $form   = $this->createForm(new VoteType($decision->getGroup()->getCity()), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Vote entity.
     *
     * @Route("/{id}", name="vote_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MongoeatVoteBundle:Vote')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Vote entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Vote entity.
     *
     * @Route("/{id}/edit", name="vote_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MongoeatVoteBundle:Vote')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Vote entity.');
        }
        $now = new \DateTime();
        if ($entity->getDecision()->getDate()->format('Ymd') != $now->format('Ymd')) {
            return $this->redirect($this->generateUrl('vote_show',array('id'=>$entity->getId())));
        }
        $editForm = $this->createForm(new VoteType($entity->getDecision()->getGroup()->getCity()), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Vote entity.
     *
     * @Route("/{id}/update", name="vote_update")
     * @Method("POST")
     * @Template("MongoeatVoteBundle:Vote:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MongoeatVoteBundle:Vote')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Vote entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new VoteType($entity->getDecision()->getGroup()->getCity()), $entity);
        $editForm->submit($request);

        if ($editForm->isValid()) {

            $now = new \DateTime();
            if ($entity->getDecision()->getDate()->format('Ymd') != $now->format('Ymd')) {
                return $this->redirect($this->generateUrl('vote_show',array('id'=>$entity->getId())));
            }

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('vote_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Vote entity.
     *
     * @Route("/{id}/delete", name="vote_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MongoeatVoteBundle:Vote')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Vote entity.');
            }

            $now = new \DateTime();
            if ($entity->getDecision()->getDate()->format('Ymd') != $now->format('Ymd')) {
                return $this->redirect($this->generateUrl('vote_show',array('id'=>$entity->getId())));
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('decision'));
    }

    /**
     * Creates a form to delete a Vote entity by id.
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
