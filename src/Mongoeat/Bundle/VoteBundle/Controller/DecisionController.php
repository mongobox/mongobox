<?php

namespace Mongoeat\Bundle\VoteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mongoeat\Bundle\VoteBundle\Entity\Decision;

/**
 * Decision controller.
 *
 * @Route("/mongoeat/decision")
 */
class DecisionController extends Controller
{
    /**
     * Lists all Decision entities.
     *
     * @Route("/", name="decision")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $group = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));

        $historique = array();
        $entities = $em->getRepository('MongoeatVoteBundle:Decision')->findByGroup($group,array('date' => 'DESC'));

        $entity = $em->getRepository('MongoeatVoteBundle:Decision')->findOneBy(array("date" => new \DateTime(),"group"=>$group));
        if (empty($entity)) {
            $entity = new Decision();
            $entity->setDate(new \DateTime());
            $entity->setGroup($group);
            $em->persist($entity);
            $em->flush();
        }

        foreach ($entities as $d) {
            if ($d!==$entity) {
                $historique[] = $d;
            }
        }
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $historique,
            $this->get('request')->query->get('page', 1)/*page number*/,
            7/*limit per page*/
        );

        $user = $this->get('security.context')->getToken()->getUser();
        $vote = $em->getRepository('MongoeatVoteBundle:Vote')->findBy(array('user'=>$user->getId(),'decision'=>$entity->getId()));

        return array(
            'pagination' => $pagination,
            'entity' => $entity,
            'hasVoted' => !empty($vote)
        );
    }

    /**
     * Lists all Decision entities.
     *
     * @Route("/small", name="decision_small")
     * @Method("GET")
     * @Template()
     */
    public function smallAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $request->getSession();
        $group = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));

        $entity = $em->getRepository('MongoeatVoteBundle:Decision')->findOneBy(array("date" => new \DateTime(),"group"=>$group));
        if (empty($entity)) {
            $entity = new Decision();
            $entity->setDate(new \DateTime());
            $entity->setGroup($group);
            $em->persist($entity);
            $em->flush();
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $vote = $em->getRepository('MongoeatVoteBundle:Vote')->findBy(array('user'=>$user->getId(),'decision'=>$entity->getId()));

        return array(
            'entity' => $entity,
            'hasVoted' => !empty($vote)
        );
    }

    /**
     * Finds and displays a Decision entity.
     *
     * @Route("/{id}", name="decision_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MongoeatVoteBundle:Decision')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Decision entity.');
        }

        return array(
            'entity'      => $entity,
        );
    }

}
