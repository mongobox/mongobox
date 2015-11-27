<?php
namespace Mongobox\Bundle\TumblrBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

use Mongobox\Bundle\TumblrBundle\Form\Type\TumblrType;
use Mongobox\Bundle\TumblrBundle\Entity\Tumblr;
use Mongobox\Bundle\TumblrBundle\Entity\TumblrVote;
use Mongobox\Bundle\TumblrBundle\Entity\TumblrTag;

/**
 * Tumblr controller.
 *
 * @Route("/tumblr")
 */
class TumblrController extends Controller
{
    protected $_limitPagination = 5;

    /**
     *
     *
     * @Route("/{page}", name="tumblr",requirements={"page" = "\d+"}, defaults={"page" = 1})
     * @Template()
     */
    public function indexAction(Request $request, $page)
    {
        $filters = array();
        $tag = $request->get('tag');
        if ($tag) {
            $filters['tag'] = $tag;
        }

        $em = $this->getDoctrine()->getManager();
        $tumblrRepository = $em->getRepository('MongoboxTumblrBundle:Tumblr');

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $entitiesTumblr = $tumblrRepository->findLast(
            $user->getGroupsIds(),
            $this->_limitPagination,
            $this->_limitPagination * ($page - 1),
            $filters
        );

        $nbTumblrEntities = count($tumblrRepository->findLast($user->getGroupsIds(), 0, 0, $filters));
        $nbPages = (int) ceil($nbTumblrEntities / $this->_limitPagination);

        return array(
            'tumblr'          => $entitiesTumblr,
            'current_filters' => $filters,
            'pagination'      => array(
                'page'        => $page,
                'page_total'  => $nbPages,
                'page_gauche' => ($page - 1 > 0) ? $page - 1 : 1,
                'page_droite' => ($page + 1 < $nbPages) ? $page + 1 : $nbPages,
                'limite'      => $this->_limitPagination
            ),
        );
    }

    /**
     *
     *
     * @Route("/add", name="tumblr_add")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $tumblr = new Tumblr();
        $form = $this->createForm(
            new TumblrType($this->get('security.token_storage')->getToken()->getUser()->getGroups()),
            $tumblr
        );

        if ($request->isMethod('POST') || $request->isXmlHttpRequest()) {
            $form->submit($request);
            if ($form->isValid()) {
                $tumblr->setDate(new \Datetime());

                // Get data tags switch request method
                if ($request->isXmlHttpRequest()) {
                    $datas = $request->request->get('tumblr');
                    $tags = $datas['tags'];
                    $access_tumblr = false;
                } else {
                    $tags = $form->get('tags')->getData();
                }

                // Set Tags
                if(!empty($tags)) {
                    foreach ($tags as $tagId) {
                        $entityTag = $em->getRepository('MongoboxTumblrBundle:TumblrTag')->find($tagId);
                        $entityTag->getTumblrs()->add($tumblr);
                    }
                }

                foreach ($form->get('groups')->getData() as $group_id) {
                    if (in_array($group_id, $user->getGroupsIds())) {
                        $access_tumblr = true;
                    }
                    $group = $em->getRepository('MongoboxGroupBundle:Group')->find($group_id);
                    $group->getTumblrs()->add($tumblr);
                }

                $em->persist($tumblr);
                $em->flush();

                if ($request->isXmlHttpRequest()) {
                    $datas = array();
                    $datas['success'] = $access_tumblr;
                    $datas['tumblrView'] = $this->render(
                        'MongoboxTumblrBundle:Slider:unTumblrSlider.html.twig',
                        array(
                            'mongo' => $tumblr
                        )
                    )->getContent();
                    $datas['showTumblr'] = ($this->getReferrerRouteName() === 'homepage') ? true : false;

                    return new Response(json_encode($datas));
                } else {
                    $this->get('session')->getFlashBag()->add('success', 'Tumblr posté avec succès');

                    return $this->redirect($this->generateUrl('tumblr'));
                }
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Template()
     * @Route("/ajax/add", name="tumblr_add_ajax")
     * @return type
     */
    public function addAjaxAction()
    {
        $tumblr = new Tumblr();
        $form = $this->createForm(
            new TumblrType($this->get('security.token_storage')->getToken()->getUser()->getGroups()),
            $tumblr
        );

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
        $value = $request->get('term');
        $em = $this->getDoctrine()->getManager();
        $tumblrTagsRepository = $em->getRepository('MongoboxTumblrBundle:TumblrTag');
        $keywords = $tumblrTagsRepository->getTags($value);

        return new Response(json_encode($keywords));
    }

    /**
     * Action to search tags for autocomplete field
     *
     * @Route("/tags-ajax-get-tag/{id_tag}", name="tumblr_tags_get_tag")
     * @Template()
     */
    public function getTagAction($id_tag)
    {
        $em = $this->getDoctrine()->getManager();
        $tag = $em->getRepository('MongoboxTumblrBundle:TumblrTag')->find($id_tag);

        return new Response($tag->getName());
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
        if (false === $resultTag) {

            // Create a new tag
            $newEntityTag = new TumblrTag();
            $newEntityTag
                ->setName($value)
                ->setSystemName($value);
            $em->persist($newEntityTag);
            $em->flush();

            // Parsing result
            $resultTag = array(
                'id'   => $newEntityTag->getId(),
                'name' => $newEntityTag->getName()
            );
        }

        return new Response(json_encode($resultTag));
    }

    /**
     * @Template()
     * @Route( "/vote/{id_tumblr}/{note}", name="tumblr_vote")
     */
    public function voteAction(Request $request, $id_tumblr, $note)
    {
        $em = $this->getDoctrine()->getManager();
        $tumblr_vote = $em->getRepository('MongoboxTumblrBundle:Tumblr')->find($id_tumblr);

        $user = $this->get('security.token_storage')->getToken()->getUser();

        //Wipe de son ancien vote
        $old_vote = $em->getRepository('MongoboxTumblrBundle:TumblrVote')->findOneBy(
            array('tumblr' => $id_tumblr, 'user' => $user)
        );
        if (!is_null($old_vote)) {
            $em->remove($old_vote);
            $em->flush();
        }
        if ((int) $note > 5) {
            $note = 5;
        } elseif ((int) $note < 0) {
            $note = 0;
        }

        $vote = new TumblrVote();
        $vote->setUser($user);
        $vote->setNote($note);
        $vote->setTumblr($tumblr_vote);

        $em->persist($vote);
        $em->flush();

        $retour = array(
            'somme'     => $tumblr_vote->getSomme(),
            'moyenne'   => $tumblr_vote->getMoyenne(),
            'info_vote' => $this->render(
                'MongoboxTumblrBundle:Slider:infoVote.html.twig',
                array('tumblr' => $tumblr_vote)
            )->getContent()
        );

        return new Response(json_encode($retour));
    }

    /**
     * @Template("MongoboxTumblrBundle:Slider:index.html.twig")
     * @Route( "/tumblr", name="tumblr_slider")
     */
    public function sliderAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $tumblr = $em->getRepository('MongoboxTumblrBundle:Tumblr')->findLast($user->getGroupsIds(), 6);

        $ajax_request = $request->isXmlHttpRequest();

        return array
        (
            'tumblr'       => $tumblr,
            'ajax_request' => $ajax_request
        );
    }

    /**
     *
     *
     * @Route("/top", name="tumblr_top")
     * @Template()
     */
    public function topAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $tumblrRepository = $em->getRepository('MongoboxTumblrBundle:TumblrVote');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $top7 = $tumblrRepository->topPeriod($user->getGroupsIds(), 7);
        $top30 = $tumblrRepository->topPeriod($user->getGroupsIds(), 30);
        $topTumblr = $tumblrRepository->top($user->getGroupsIds());

        return array(
            'top7'      => $top7,
            'top30'     => $top30,
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

        $retour = array(
            'content' => $this->render(
                'MongoboxTumblrBundle:Slider:popoverContent.html.twig',
                array('tumblr' => $tumblr, 'tumblr_class' => $class_tumblr)
            )->getContent(),
            'title'   => $this->render('MongoboxTumblrBundle:Slider:titlePopover.html.twig', array('tumblr' => $tumblr))
                ->getContent()
        );

        return new Response(json_encode($retour));
    }

    /**
     * Finds and displays a Tumblr entity.
     *
     * @Route("/show/{id}", name="tumblr_show")
     * @Template()
     */
    public function showAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $tumblrRepository = $em->getRepository('MongoboxTumblrBundle:Tumblr');
        $entityTumblr = $tumblrRepository->findOneByGroup($id, $user->getGroupsIds());

        if (!$entityTumblr) {
            throw $this->createNotFoundException('Unable to find Tumblr entity.');
        }

        $entityNext = $tumblrRepository->getNextEntity($entityTumblr->getId(), $user->getGroupsIds());
        $entityPrev = $tumblrRepository->getPrevEntity($entityTumblr->getId(), $user->getGroupsIds());


        return array(
            'entity'     => $entityTumblr,
            'entityPrev' => $entityPrev,
            'entityNext' => $entityNext
        );
    }

    /**
     * @Route("/propose_votes/{another}", name="tumblr_propose_votes")
     * @Template()
     */
    public function proposeVotesAction(Request $request, $another)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //Traitement en Ajax
        if ($another) {
            $tumblrs_exclude = $request->request->get('tumblrs');
            $tumblrs = $em->getRepository('MongoboxTumblrBundle:TumblrVote')->getProposeTumblrVotes(
                $user,
                1,
                $tumblrs_exclude
            );
            if (count($tumblrs) > 0) {
                $html = $this->render(
                    'MongoboxTumblrBundle:Slider:unTumblrSlider.html.twig',
                    array('mongo' => $tumblrs[0]['tumblr'], 'loadAnother' => true)
                )->getContent();
                $done = false;
            } else {
                $html = '';
                $done = true;
            }

            return new Response(
                json_encode(
                    array(
                        'html' => $html,
                        'done' => $done
                    )
                )
            );
        } else {
            $tumblrs = $em->getRepository('MongoboxTumblrBundle:TumblrVote')->getProposeTumblrVotes($user, 3);

            return array(
                'tumblrs' => $tumblrs
            );
        }
    }

    /**
     * Fonction pour récupérer la route referrer
     */
    public function getReferrerRouteName()
    {
        $request = $this->get('request');
        $baseUrl = $request->getBaseUrl();

        $referer = $request->headers->get('referer');
        $lastPath = substr($referer, strpos($referer, $baseUrl));
        $lastPath = str_replace($baseUrl, '', $lastPath);

        $matcher = $this->get('router')->getMatcher();
        $parameters = $matcher->match($lastPath);
        $route = $parameters['_route'];

        return $route;
    }
}