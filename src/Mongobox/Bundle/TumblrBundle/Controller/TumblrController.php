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
    public function indexAction(Request $resquest, $page)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $tumblrRepository = $em->getRepository('MongoboxTumblrBundle:Tumblr');
		$group = $em->getRepository('MongoboxGroupBundle:Group')->find($session->get('id_group'));

        $entitiesMongoPute = $tumblrRepository->findBy(
                array(),
                array('date' => 'DESC'),
                $this->_limitPagination,
                $this->_limitPagination * ($page-1)

        );

        $nbPages = (int) (count($tumblrRepository->findAll())  / $this->_limitPagination);

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
     * @Route( "/tumblr_vote/{id_tumblr}/{sens}", name="tumblr_vote")
     */
    public function voteAction(Request $request, $sens, $id_tumblr)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $tumblr_vote = $em->getRepository('MongoboxTumblrBundle:Tumblr')->find($id_tumblr);

        //Wipe de son ancien vote
        $old_vote = $em->getRepository('MongoboxTumblrBundle:TumblrVote')->findOneBy(array('tumblr' => $id_tumblr, 'ip' => $_SERVER['REMOTE_ADDR']));
        if (!is_null($old_vote)) {
            $em->remove($old_vote);
            $em->flush();
        }

        $vote = new TumblrVote();
        $vote->setIp($_SERVER['REMOTE_ADDR']);
        $vote->setSens($sens);
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

        $mongo_pute = $em->getRepository('MongoboxTumblrBundle:Tumblr')->findLast(5, $user->getGroupsIds());
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
	public function topAction(Request $resquest){
		
		$em = $this->getDoctrine()->getEntityManager();
		$tumblrRepository = $em->getRepository('MongoboxTumblrBundle:TumblrVote');
		
		$top7 = $tumblrRepository->topPeriod();
		$top30 = $tumblrRepository->topPeriod(30);
		$topTumblr = $tumblrRepository->top();
		
		return array(
			'top7' => $top7,
			'top30' => $top30,
			'topTumblr' => $topTumblr
		);
	}
}