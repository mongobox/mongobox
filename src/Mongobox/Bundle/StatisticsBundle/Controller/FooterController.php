<?php

namespace Mongobox\Bundle\StatisticsBundle\Controller;

use Mongobox\Bundle\StatisticsBundle\Statistics;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class FooterController extends Controller
{
    /**
     * @Route("/footer/statistics")
     * @Template()
     */
    public function statisticsAction(Request $request)
    {
        $dm = $this->getDoctrine()->getManager();

        $activities = $dm
            ->getRepository('MongoboxStatisticsBundle:User\Activity')
            ->getActiveUsers()
        ;

        $currentGroupId = (int) $request->cookies->get('id_group', 0);
        $session = $request->getSession();
        $currentGroupId = $session->get('id_group');

        $videosRepository   = $dm->getRepository('MongoboxJukeboxBundle:Videos');
        $videosGroupCount   = $videosRepository->getCount($currentGroupId);
        $videosGlobalCount  = $videosRepository->getCount();

        $tumblrRepository   = $dm->getRepository('MongoboxTumblrBundle:Tumblr');
        $imagesGroupCount   = $tumblrRepository->getCount($currentGroupId);
        $imagesGlobalCount  = $tumblrRepository->getCount();

        $usersRepository    = $dm->getRepository('MongoboxUsersBundle:User');
        $usersGroupCount    = $usersRepository->getCount($currentGroupId);
        $usersGlobalCount   = $usersRepository->getCount();
        $lastRegistered     = $usersRepository->getLastRegistered();

        $connectionsPeak    = $dm
            ->getRepository('MongoboxStatisticsBundle:User\Connection')
            ->getMaximumPeak()
        ;

        return array(
            'activities'        => $activities,
            'current_group_id'  => $currentGroupId,

            'videos'    => array(
                'group_count'   => $videosGroupCount,
                'global_count'  => $videosGlobalCount
            ),

            'images'    => array(
                'group_count'    => $imagesGroupCount,
                'global_count'   => $imagesGlobalCount
            ),

            'users'     => array(
                'group_count'       => $usersGroupCount,
                'global_count'      => $usersGlobalCount,
                'last_registered'   => $lastRegistered,
                'connections_peak'  => array(
                    'number'    => $connectionsPeak->getNumber(),
                    'date'      => $connectionsPeak->getDate()->format('d/m/Y'),
                    'time'      => $connectionsPeak->getTime()->format('H:i')
                )
            )
        );
    }
}
