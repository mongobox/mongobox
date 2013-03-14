<?php

namespace Mongobox\Bundle\TumblrBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Mongobox\Bundle\TumblrBundle\Entity\Tumblr;
use Mongobox\Bundle\TumblrBundle\Form\TumblrType;

/**
 * Tumblr controller.
 *
 * @Route("/admin/tumblr")
 */
class TumblrController extends Controller
{
	protected $_limitPagination = 20;
    /**
     * Lists all Tumblr entities.
     *
     * @Route("/{page}", name="admin_tumblr",requirements={"page" = "\d+"}, defaults={"page" = 1})
     * @Template()
     */
    public function indexAction(Request $request, $page)
    {
        $em = $this->getDoctrine()->getManager();

        $tumblrRepository = $em->getRepository('MongoboxTumblrBundle:Tumblr');
        $user = $this->get('security.context')->getToken()->getUser();

        $entities = $tumblrRepository->findLast($user->getGroupsIds(), $this->_limitPagination, $this->_limitPagination * ($page-1));

        $nbPages = (int) (count($tumblrRepository->findLast($user->getGroupsIds()))  / $this->_limitPagination);
        
        return array(
            'entities' => $entities,
        	'pagination' => array(
        			'page' => $page,
        			'page_total' => $nbPages,
        			'page_gauche' => ( $page-1 > 0 ) ? $page-1 : 1,
        			'page_droite' => ( $page+1 < $nbPages ) ? $page+1 : $nbPages,
        			'limite' =>  $this->_limitPagination
        	),
        );
    }

    /**
     * Displays a form to edit an existing Tumblr entity.
     *
     * @Route("/{id}/edit", name="admin_tumblr_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MongoboxTumblrBundle:Tumblr')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tumblr entity.');
        }

        $groups =$this->get('security.context')->getToken()->getUser()->getGroups();
        $editForm = $this->createForm(new TumblrType($groups), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Tumblr entity.
     *
     * @Route("/{id}/update", name="admin_tumblr_update")
     * @Method("POST")
     * @Template("MongoboxTumblrBundle:Tumblr:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

		$tumblrRepository = $em->getRepository('MongoboxTumblrBundle:Tumblr');
        $entity = $tumblrRepository->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tumblr entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        $groups =$this->get('security.context')->getToken()->getUser()->getGroups();
        $editForm = $this->createForm(new TumblrType($groups), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
			// Clean tags
            $tumblrRepository->cleanTags($entity);
			   
			// Save tags
            foreach($editForm->get('tags')->getData() as $tag_id)
            {
                $entityTag = $em->getRepository('MongoboxTumblrBundle:TumblrTag')->find($tag_id);
                $entityTag->getTumblrs()->add($entity);
            }
			
			// Clean cleanGroups			
            //$tumblrRepository->cleanGroups($entity);
			
			// Save Groups
            foreach($editForm->get('groups')->getData() as $group_id)
            {
                $group = $em->getRepository('MongoboxGroupBundle:Group')->find($group_id);
                $group->getTumblrs()->add($entity);
            }

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_tumblr_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Tumblr entity.
     *
     * @Route("/{id}/delete", name="admin_tumblr_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MongoboxTumblrBundle:Tumblr')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Tumblr entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_tumblr'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
