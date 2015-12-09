<?php
namespace Mongobox\Bundle\JukeboxBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class UserVideosController
 *
 * @category    Mongobox
 * @package     Mongobox\Bundle\JukeboxBundle\Controller
 */
class UserVideosController extends Controller
{
    public function manageVideosBlockAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $session = $request->getSession();
        $groupId = $session->get('id_group');

        $videos =
            $em->getRepository('MongoboxJukeboxBundle:VideoGroup')->getDisabledVideosByUser($groupId, $user->getId());

        return $this->render(
            'MongoboxJukeboxBundle:Blocs:manageVideos.html.twig',
            array('videos' => $videos)
        );
    }
}
