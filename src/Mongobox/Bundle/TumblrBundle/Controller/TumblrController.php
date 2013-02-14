<?php
namespace Mongobox\Bundle\TumblrBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Mongobox\Bundle\TumblrBundle\Form\TumblrType;
use Mongobox\Bundle\TumblrBundle\Entity\Tumblr;
use Mongobox\Bundle\TumblrBundle\Entity\TumblrVote;
use Mongobox\Bundle\TumblrBundle\Entity\TumblrTag;

/**
 * Page pute controller.
 *
 * @Route("/mongo-pute")
 */
class TumblrController extends Controller
{
    protected $_limitPagination = 5;

    /**
     *
     *
     * @Route("/{page}", name="mongo_pute",requirements={"page" = "\d+"}, defaults={"page" = 1})
     * @Template()
     */
    public function indexAction(Request $request, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $tumblrRepository = $em->getRepository('MongoboxTumblrBundle:Tumblr');
		$session = $request->getSession();
		$user = $this->get('security.context')->getToken()->getUser();

        $entitiesMongoPute = $tumblrRepository->findLast($user->getGroupsIds(), $this->_limitPagination, $this->_limitPagination * ($page-1));

        $nbPages = (int) (count($tumblrRepository->findLast($user->getGroupsIds()))  / $this->_limitPagination);

        return array(
            'mongo_pute' => $entitiesMongoPute,
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
     *
     *
     * @Route("/add", name="mongo_pute_add")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $tumblr = new Tumblr();
        $form = $this->createForm(new TumblrType($this->get('security.context')->getToken()->getUser()->getGroups()), $tumblr);

        if ( 'POST' === $request->getMethod() ) {
            $form->bind($request);
            if ( $form->isValid() )
			{
                $tumblr->setDate(new \Datetime());

                // Set Tags
                foreach($form->get('tags')->getData() as $tag_id)
                {
                    $entityTag = $em->getRepository('MongoboxTumblrBundle:TumblrTag')->find($tag_id);
                    $entityTag->getTumblrs()->add($tumblr);
                }

				foreach($form->get('groups')->getData() as $group_id)
				{
					$group = $em->getRepository('MongoboxGroupBundle:Group')->find($group_id);
					$group->getTumblrs()->add($tumblr);
				}

                $em->persist($tumblr);
                $em->flush();
                $this->get('session')->setFlash('success', 'Tumblr posté avec succès');

                return $this->redirect($this->generateUrl('mongo_pute'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * Action to search tags for autocomplete field
     *
     * @Route("/tags-ajax-autocomplete", name="tumblr_tags_ajax_autocomplete")
     * @Template()
     */
    public function ajaxAutocompleteTagsAction(Request $request)
    {
        // récupération du mots clés en ajax selon la présélection du mot
        $value = $request->get('tags');
        $em = $this->getDoctrine()->getManager();
        $tumblrTagsRepository = $em->getRepository('MongoboxTumblrBundle:TumblrTag');
        $motscles = $tumblrTagsRepository->getTags($value);

        return new Response(json_encode($motscles));
    }

    /**
     * Action to load tag or create it if not exist
     *
     * @Route("/tags-load-item", name="tumblr_tags_load_item")
     * @Template()
     */
    public function ajaxLoadTagAction(Request $request)
    {
        // récupération du mots clés en ajax selon la présélection du mot
        $value = $request->get('tag');


        $em = $this->getDoctrine()->getManager();
        $tumblrTagsRepository = $em->getRepository('MongoboxTumblrBundle:TumblrTag');

        // Check if tag Already exist
        $resultTag = $tumblrTagsRepository->loadOneTagByName($value);
        if( false === $resultTag ){

            // Create a new tag
            $newEntityTag = new TumblrTag();
            $newEntityTag
                ->setName($value)
                ->setSystemName($value)
            ;
            $em->persist($newEntityTag);
            $em->flush();

            // Parsing result
            $resultTag = array(
                'id' => $newEntityTag->getId(),
                'name' => $newEntityTag->getName()
            );
        }

        return new Response(json_encode($resultTag));
    }

    /**
     * @Template()
     * @Route( "/tumblr_vote/{id_tumblr}/{note}", name="tumblr_vote")
     */
    public function voteAction(Request $request, $id_tumblr, $note)
    {
        $em = $this->getDoctrine()->getManager();
        $tumblr_vote = $em->getRepository('MongoboxTumblrBundle:Tumblr')->find($id_tumblr);
		$session = $request->getSession();
		$user = $this->get('security.context')->getToken()->getUser();
        //Wipe de son ancien vote
        $old_vote = $em->getRepository('MongoboxTumblrBundle:TumblrVote')->findOneBy(array('tumblr' => $id_tumblr, 'user' => $user));
        if (!is_null($old_vote)) {
            $em->remove($old_vote);
            $em->flush();
        }
		if((int)$note > 5) $note = 5;
		elseif((int)$note < 0) $note = 0;

        $vote = new TumblrVote();
        $vote->setUser($user);
        $vote->setNote($note);
        $vote->setTumblr($tumblr_vote);

        $em->persist($vote);
        $em->flush();
        
        $retour = array(
        		'somme' => $tumblr_vote->getSomme(),
        		'moyenne' => $tumblr_vote->getMoyenne()
        );

        return new Response(json_encode($retour));
    }

    /**
     * @Template()
     * @Route( "/tumblr", name="tumblr")
     */
    public function tumblrAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
		$session = $request->getSession();
		$user = $this->get('security.context')->getToken()->getUser();

        $mongo_pute = $em->getRepository('MongoboxTumblrBundle:Tumblr')->findLast($user->getGroupsIds(), 5);
        return array
        (
            'mongo_pute' => $mongo_pute
        );
    }

	/**
	 *
	 *
	 * @Route("/top", name="tumblr_top")
	 * @Template()
	 */
	public function topAction(Request $request){
		
		$em = $this->getDoctrine()->getManager();
		$tumblrRepository = $em->getRepository('MongoboxTumblrBundle:TumblrVote');
		$session = $request->getSession();
		$group = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));

		$top7 = $tumblrRepository->topPeriod($group);
		$top30 = $tumblrRepository->topPeriod($group, 30);
		$topTumblr = $tumblrRepository->top($group);
		
		return array(
			'top7' => $top7,
			'top30' => $top30,
			'topTumblr' => $topTumblr
		);
	}
	
	/**
	 * @Route("/load/popover/content/{id_tumblr}", name="tumblr_load_popover_content")
	 * @param Request $request
	 */
	public function loadPopoverContentAction(Request $request, $id_tumblr)
	{
		$em = $this->getDoctrine()->getManager();
		$tumblr = $em->getRepository('MongoboxTumblrBundle:Tumblr')->find($id_tumblr);
		$class_tumblr = $request->request->get('class_tumblr');
		
		return $this->render('MongoboxTumblrBundle:Tumblr:popoverContent.html.twig', array(
				'tumblr' => $tumblr,
				'tumblr_class' => $class_tumblr
		));
	}

    /**
     * Finds and displays a Tumblr entity.
     *
     * @Route("/show/{id}", name="tumblr_show")
     * @Template()
     */
    public function showAction($id)
    {
       // var_dump(__METHOD__,$id);exit;
        $em = $this->getDoctrine()->getManager();

        $tumblrRepository = $em->getRepository('MongoboxTumblrBundle:Tumblr');
        $entityTumblr = $tumblrRepository->find($id);

        if (!$entityTumblr) {
            throw $this->createNotFoundException('Unable to find Tumblr entity.');
        }

        $entityNext = $tumblrRepository->getNextEntity($entityTumblr->getId());
        $entityPrev = $tumblrRepository->getPrevEntity($entityTumblr->getId());


        return array(
            'entity'    => $entityTumblr,
            'entityPrev' => $entityPrev,
            'entityNext' => $entityNext
        );
    }
}