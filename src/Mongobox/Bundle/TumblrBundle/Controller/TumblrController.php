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
        $em = $this->getDoctrine()->getEntityManager();
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
        $em = $this->getDoctrine()->getEntityManager();

        $tumblr = new Tumblr();
        $form = $this->createForm(new TumblrType($this->get('security.context')->getToken()->getUser()->getGroups()), $tumblr);

        if ( 'POST' === $request->getMethod() ) {
            $form->bindRequest($request);
            if ( $form->isValid() )
			{
                $tumblr->setDate(new \Datetime());
                $em->persist($tumblr);
                $em->flush();
				foreach($form->get('groups')->getData() as $group_id)
				{
					$group = $em->getRepository('MongoboxGroupBundle:Group')->find($group_id);
					$group->getTumblrs()->add($tumblr);
				}
                $em->flush();
                $this->get('session')->setFlash('success', 'Tumblr posté avec succès');

                return $this->redirect($this->generateUrl('wall_index'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Template()
     * @Route( "/tumblr_vote/{id_tumblr}/{note}", name="tumblr_vote")
     */
    public function voteAction(Request $request, $note, $id_tumblr)
    {
        $em = $this->getDoctrine()->getEntityManager();
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

        return new Response($tumblr_vote->getSomme());
    }

    /**
     * @Template()
     * @Route( "/tumblr", name="tumblr")
     */
    public function tumblrAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
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
		
		$em = $this->getDoctrine()->getEntityManager();
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
}