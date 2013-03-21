<?php

namespace Mongobox\Bundle\JukeboxBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Mongobox\Bundle\JukeboxBundle\Entity\Videos;
use Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup;
use Mongobox\Bundle\JukeboxBundle\Entity\Playlist;
use Mongobox\Bundle\JukeboxBundle\Entity\Dedicaces;

// Forms
use Mongobox\Bundle\JukeboxBundle\Form\VideosType;
use Mongobox\Bundle\JukeboxBundle\Form\SearchVideosType;
use Mongobox\Bundle\JukeboxBundle\Form\MultiDedicacesType;

/**
 * Videos controller.
 *
 * @Route("/videos")
 */
class VideosController extends Controller
{
    protected $_limitPagination = 50;

    /**
     * Lists all Videos entities.
     *
     * @Route("/{page}", name="videos",requirements={"page" = "\d+"}, defaults={"page" = 1})
     * @Template()
     */
    public function indexAction(Request $request, $page)
    {
        $em = $this->getDoctrine()->getManager();
		$group = $em->getRepository('MongoboxGroupBundle:Group')->find($request->getSession()->get('id_group'));
        $videosRepository = $em->getRepository('MongoboxJukeboxBundle:Videos');
        $videoGroupRepository = $em->getRepository('MongoboxJukeboxBundle:VideoGroup');

        $formSearchVideos = $this->createForm(new SearchVideosType());

        $criteria = array();
         if (  'POST' === $request->getMethod() ) {
             $formSearchVideos->bind($request);
             $criteria = array('title' => $formSearchVideos->get('search')->getData());
         }

     	// filtre par defaut
        $filters = array('sortBy' => 'vg.lastBroadcast', 'orderBy' => 'desc');

        // $_GET parameters
        $sortBy = $request->query->get('sortBy');
        $orderBy = $request->query->get('orderBy');
        
        if( !empty($sortBy) && !empty($orderBy) ){
        	$filters = array(
        		'sortBy' => $sortBy,
        		'orderBy' => $orderBy		
        	);
        }

        $entities = $videosRepository->search(
				$group,
                $criteria,
                $page,
                $this->_limitPagination,
        		$filters
        );

        $nbPages = (int) (count($videoGroupRepository->findBy(array('group' => $group->getId())))  / $this->_limitPagination);

        $displayFilters = $filters;
        ( 'DESC' === $displayFilters['orderBy'] ) ? $displayFilters['orderBy'] = 'ASC' : $displayFilters['orderBy'] = 'DESC';
        
        return array(
            'searchVideosForm' => $formSearchVideos->createView(),
            'entities' => $entities,
            'pagination' => array(
                'page' => $page,
                'page_total' => $nbPages,
                'page_gauche' => ( $page-1 > 0 ) ? $page-1 : 1,
                'page_droite' => ( $page+1 < $nbPages ) ? $page+1 : $nbPages,
                'limite' =>  $this->_limitPagination
            ),
        	'filters' => $filters
        );
    }

    /**
     * Finds and displays a Videos entity.
     *
     * @Route("/{id}/show", name="videos_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MongoboxJukeboxBundle:Videos')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Videos entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Videos entity.
     *
     * @Route("/{id}/edit", name="videos_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MongoboxJukeboxBundle:Videos')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Videos entity.');
        }

        $editForm = $this->createForm(new VideosType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Videos entity.
     *
     * @Route("/{id}/update", name="videos_update")
     * @Method("POST")
     * @Template("MongoboxJukeboxBundle:Videos:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MongoboxJukeboxBundle:Videos')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Videos entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new VideosType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('videos_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Videos entity.
     *
     * @Route("/{id}/delete", name="videos_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('MongoboxJukeboxBundle:Videos')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Videos entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('videos'));
    }

    /**
     * Add the video to the playlist entity.
     *
     * @Route("/{id}/add_to_playlist", name="videos_add_to_playlist")
     * @ParamConverter("video", class="MongoboxJukeboxBundle:VideoGroup")
     */
    public function addToPlaylistAction(Request $request, VideoGroup $video)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $group = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));

        $playlist_add = new Playlist();
        $playlist_add->setVideoGroup($video);
        $playlist_add->setGroup($group);
        $playlist_add->setRandom(0);
        $playlist_add->setCurrent(0);
        $playlist_add->setDate(new \Datetime());
        $em->persist($playlist_add);
        $em->flush();

        return $this->redirect($this->generateUrl('videos'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
            ;
    }

    /**
     * Add the video to the playlist entity.
     *
     * @Route("/{id}/add_to_playlist_width_dedicace", name="videos_add_to_playlist_width_dedicace")
     * @ParamConverter("video", class="MongoboxJukeboxBundle:VideoGroup")
     * @Template("MongoboxJukeboxBundle:Videos/Blocs:addDedicace.html.twig")
     */
    public function addToPlaylistWidthDedicaceAction(Request $request, VideoGroup $video)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();

        $permanantDedicaces = $em->getRepository('MongoboxJukeboxBundle:Dedicaces')->testVideoGroups($video->getVideo()->getId(), $user->getGroupsIds());

        if (count($permanantDedicaces) == 0) {
            $permanant = "add";
        } else {
            $permanant = "";
        }
        $permanente = new Dedicaces();
        $permanente->setVideo($video->getVideo());

        $playList = new Dedicaces();
        $playList->setVideo($video->getVideo());

        $dedicaces = array(
                            "permanente"        => $permanente->setPermanant(true),
                            "playList"          => $playList->setPermanant(false),
                            "buffPermanente"    => $permanant
                          );

        $formDedicace = $this->createForm(new MultiDedicacesType($this->get('security.context')->getToken()->getUser()->getGroups()), $dedicaces);

        if ( 'POST' === $request->getMethod() ) {
            $formDedicace->bind($request);

            $formDatas = $formDedicace->getData();

            $postDatas = $request->request->get($formDedicace->getName());

            /* sauvegarde de la dédicace parmanante */
            if ($permanente->getText()) {
                $permanente = $em->getRepository('MongoboxJukeboxBundle:Dedicaces')->setGroupById($permanente, $postDatas['permanente']['groups'], true);
                if ($permanente) {
                    $em->persist($permanente);
                }
            }

            /* sauvegarde de la dédicace pour la playliste */
            if ($playList->getText()) {
                $playList = $em->getRepository('MongoboxJukeboxBundle:Dedicaces')->setGroupById($playList, $postDatas['playList']['groups']);
                $em->persist($playList);
            }

            $em->flush();

            return $this->redirect($this->generateUrl('videos_add_to_playlist', array("id" => $video->getId())));

        }

        return array(
            'formDedicace'  => $formDedicace->createView(),
            'permanent'     => $permanant,
            'videoId'       => $video->getId()
        );
    }
}
