<?php

namespace Mongobox\Bundle\StatisticsBundle\Controller;

use Mongobox\Bundle\StatisticsBundle\Statistics;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/footer/stats", name="footer_stats")
     * @Template()
     */
    public function footerAction(Request $request)
    {
        $dm = $this->getDoctrine()->getManager();

        $activities = $dm
            ->getRepository('MongoboxStatisticsBundle:User\Activity')
            ->getActiveUsers()
        ;

        $currentGroupId = (int) $request->cookies->get('id_group', 0);

        return array(
            'activities'            => $activities,
            'current_group_id'      => $currentGroupId,
            'global_count_videos'   => 'x',
            'global_count_images'   => 'x',
            'global_count_users'    => 'x'
        );
    }
}
