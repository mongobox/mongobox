<?php

namespace Mongobox\Bundle\JukeboxBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Mongobox\Bundle\JukeboxBundle\Entity\Videos;
use Mongobox\Bundle\JukeboxBundle\Entity\VideoTag;
use Mongobox\Bundle\JukeboxBundle\Entity\VideoGroup;
use Mongobox\Bundle\JukeboxBundle\Entity\Playlist;

// Forms
use Mongobox\Bundle\JukeboxBundle\Form\VideosType;
use Mongobox\Bundle\JukeboxBundle\Form\VideoType;
use Mongobox\Bundle\JukeboxBundle\Form\VideoSearchType;
use Mongobox\Bundle\JukeboxBundle\Form\VideoInfoType;
use Mongobox\Bundle\JukeboxBundle\Form\SearchVideosType;
use Mongobox\Bundle\JukeboxBundle\Form\VideoTagsType;

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
     * @ParamConverter("video", class="MongoboxJukeboxBundle:Videos")
     */
    public function addToPlaylistAction(Request $request, Videos $video)
    {
        $em = $this->getDoctrine()->getManager();
		$session = $request->getSession();
		$is_added = false;
		$group = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));
		$videoGroup = $em->getRepository('MongoboxJukeboxBundle:VideoGroup')->findOneBy(array('group' => $group, 'video' => $video));
		if(is_object($videoGroup))
		{
			$playlist_add = new Playlist();
			$playlist_add->setVideoGroup($videoGroup);
			$playlist_add->setGroup($group);
			$playlist_add->setRandom(0);
			$playlist_add->setCurrent(0);
			$playlist_add->setDate(new \Datetime());
			$em->persist($playlist_add);
			$em->flush();
			$is_added = true;
		}

		$retour = array(
			'success' => $is_added,
			'message' => ($is_added) ? "Vidéo ajoutée à la playlist": "Echec lors de l'ajout",
		);

		return new Response(json_encode($retour));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }

    /**
     * Action to search tags for autocomplete field
     *
     * @Route("/video-tags-ajax-autocomplete", name="video_tags_ajax_autocomplete")
     * @Template()
     */
    public function videoAjaxAutocompleteTagsAction(Request $request)
    {
        // récupération du mots clés en ajax selon la présélection du mot
        $value = $request->get('term');
        $em = $this->getDoctrine()->getManager();
        $videoTagsRepository = $em->getRepository('MongoboxJukeboxBundle:VideoTag');
        $motscles = $videoTagsRepository->getTags($value);

        return new Response(json_encode($motscles));
    }

    /**
     * Action to load tag or create it if not exist
     *
     * @Route("/video-tags-load-item", name="video_tags_load_item")
     * @Template()
     */
    public function ajaxLoadTagAction(Request $request)
    {
        // récupération du mots clés en ajax selon la présélection du mot
        $value = $request->get('tag');


        $em = $this->getDoctrine()->getManager();
        $videoTagsRepository = $em->getRepository('MongoboxJukeboxBundle:VideoTag');

        // Check if tag Already exist
        $resultTag = $videoTagsRepository->loadOneTagByName($value);
        if( false === $resultTag ){

            // Create a new tag
            $newEntityTag = new VideoTag();
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
     * @Route( "/post_video", name="post_video")
     */
	public function postVideoAction(Request $request)
	{
		$em = $this->getDoctrine()->getEntityManager();
		$user = $this->get('security.context')->getToken()->getUser();
		$session = $request->getSession();
		$group = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));

		$video = new Videos();
		$form_video = $this->createForm(new VideoType(), $video);
		$form_search = $this->createForm(new VideoSearchType(), $video);
		if ( 'POST' === $request->getMethod() )
		{
			$form_video->bindRequest($request);
			if ( $form_video->isValid() )
			{
				$video->setLien(Videos::parse_url_detail($video->getLien()));
				//On vérifie qu'elle n'existe pas déjà
				$video_new = $em->getRepository('MongoboxJukeboxBundle:Videos')->findOneby(array('lien' => $video->getLien()));
				if (!is_object($video_new))
				{
					$dataYt = Videos::getDataFromYoutube($video->getLien());

					$video->setDate(new \Datetime())
							->setTitle( $dataYt->title )
							->setDuration($dataYt->duration)
							->setThumbnail( $dataYt->thumbnail->hqDefault )
							->setThumbnailHq( $dataYt->thumbnail->sqDefault )
							->setArtist($request->request->get('artist'))
							->setSongName($request->request->get('songName'));
					$em->persist($video);
					$em->flush();
					$video_new = $video;

					$this->get('session')->setFlash('success', 'Vidéo "'.$dataYt->title .'" postée avec succès');
				}
				//On vérifie qu'elle n'existe pas pour ce groupe
				$video_group = $em->getRepository('MongoboxJukeboxBundle:VideoGroup')->findOneby(array('video' => $video_new, 'group' => $group));
				if(!is_object($video_group))
				{
					$video_group = new VideoGroup();
					$video_group->setVideo($video_new)
								->setGroup($group)
								->setUser($user)
								->setDiffusion(0)
								->setVolume(50)
								->setVotes(0);
					$em->persist($video_group);
					$em->flush();
				}
				//On l'ajoute à la playlist
				$playlist_add = new Playlist();
				$playlist_add->setVideoGroup($video_group)
								->setGroup($group)
								->setDate(new \Datetime())
								->setRandom(0)
								->setCurrent(0);
				$em->persist($playlist_add);

				$em->flush();
				$form_video_info = $this->createForm(new VideoInfoType(), $video_new);

				//On récupère tous les tags de cette chanson
				$list_tags = $em->getRepository('MongoboxJukeboxBundle:VideoTag')->getVideoTags($video_new);

				$content = $this->render('MongoboxJukeboxBundle:Partial:edit-modal.html.twig', array(
								'form_video_info' => $form_video_info->createView(),
								'video' => $video_new,
								'list_tags' => $list_tags
							))->getContent();
				$title = 'Informations de la vidéo : '.$video_new->getName();

				$return = array(
					'content' => $content,
					'title' => $title
				);
				return new Response(json_encode($return));
			}
		}

		$content = $this->render("MongoboxCoreBundle:Wall/Blocs:postVideo.html.twig", array(
						'form_video' => $form_video->createView(),
						'form_search' => $form_search->createView()
					))->getContent();
		$title = 'Ajout d\'une vidéo';

		$return = array(
			'content' => $content,
			'title' => $title
		);

		return new Response(json_encode($return));
	}

    /**
     * Action to edit a video from a modal
     *
     * @Route("/edit_modal/{id_video}", name="video_edit_modal")
	 * @ParamConverter("video", class="MongoboxJukeboxBundle:Videos", options={"id" = "id_video"})
     */
    public function editVideoModalAction(Request $request, Videos $video)
    {
        $em = $this->getDoctrine()->getManager();

		$editForm = $this->createForm(new VideoInfoType(), $video);
        // Process the form on POST
        if ($request->isMethod('POST'))
		{
            $editForm->bind($request);
            if ( $editForm->isValid() )
			{
				//On supprime les anciens tags de la vidéo
				$em->getRepository('MongoboxJukeboxBundle:Videos')->wipeTags($video);

				//On rajoute les tags
				$tags = $editForm->get('tags')->getData();
				if(is_array($tags))
				{
					foreach($tags as $tag_id)
					{
						$entityTag = $em->getRepository('MongoboxJukeboxBundle:VideoTag')->find($tag_id);
						$entityTag->getVideos()->add($video);
					}
				}
				$em->flush();

				$content = 'Modification enregistrée avec succès';
				$title = '';

				$return = array(
					'content' => $content,
					'title' => $title
				);
				return new Response(json_encode($return));
			};
		};

		$list_tags = $em->getRepository('MongoboxJukeboxBundle:VideoTag')->getVideoTags($video);

		$content = $this->render('MongoboxJukeboxBundle:Partial:edit-modal.html.twig', array(
						'form_video_info' => $editForm->createView(),
						'video' => $video,
						'list_tags' => $list_tags
					))->getContent();
		$title = 'Edition de la vidéo : '.$video->getName();

		$return = array(
			'content' => $content,
			'title' => $title
		);
        return new Response(json_encode($return));
    }

    /**
     * Action to search tags for autocomplete field
     *
     * @Route("/tags-ajax-autocomplete", name="video_tags_ajax_autocomplete")
     */
    public function ajaxAutocompleteTagsAction(Request $request)
    {
        // récupération du mots clés en ajax selon la présélection du mot
        $value = $request->get('term');
        $em = $this->getDoctrine()->getManager();
        $videoTagsRepository = $em->getRepository('MongoboxJukeboxBundle:VideoTag');
        $motscles = $videoTagsRepository->getTags($value);

        return new Response(json_encode($motscles));
    }

    /**
     * Action to search video from mongobox or youtube
     *
     * @Route("/ajax/search/keyword", name="ajax_search_keyword")
     */
    public function ajaxSearchKeywordAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
		$session = $request->getSession();
		$group = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));
		$video = new Videos();
		$form_search = $this->createForm(new VideoSearchType(), $video);
		$youtube_video = array();
		$mongobox_video = array();
		if ( 'POST' === $request->getMethod() )
		{
			$form_search->bindRequest($request);
			$keyword = $form_search->get('search')->getData();

			//Récupération des infos de Youtube
			$url = 'http://gdata.youtube.com/feeds/api/videos?q='.$keyword.'&max-results=10';
			$videos = @simplexml_load_file( $url );
			foreach($videos->entry as $video)
			{
				$att = 'href';
				$url = $video->link[0]->attributes()->$att;
				$youtube_video[] = array('title' => strip_tags($video->title->asXML()), 'url' => $url);
			}

			//Récupération des infos Mongobox
			$search = array('title' => $keyword);
			$mongobox_videos = $em->getRepository('MongoboxJukeboxBundle:Videos')->search($group, $search, 1, 10);
			foreach($mongobox_videos as $mv)
			{
				$mongobox_video[] = array('title' => $mv->getVideo()->getName(), 'url' => $mv->getVideo()->getLien());
			}
		}

        return new Response(json_encode(array(
			'youtube' => $this->render('MongoboxJukeboxBundle:Partial:search-listing.html.twig', array(
						'video_listing' => $youtube_video,
						'title' => 'Youtube'
					))->getContent(),
			'mongobox' => $this->render('MongoboxJukeboxBundle:Partial:search-listing.html.twig', array(
						'video_listing' => $mongobox_video,
						'title' => 'Mongobox'
					))->getContent()
			)));
    }
}