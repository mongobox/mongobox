<?php

namespace Mongobox\Bundle\StatisticsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Mongobox\Bundle\StatisticsBundle\Statistics;

class JukeboxController extends Controller
{
    /**
     * @Route("/jukebox/stats", name="jukebox_stats")
     * @Template()
     */
    public function indexAction()
    {
        $dm     = $this->getDoctrine()->getManager();
        $object = new Statistics\Jukebox($dm);

        return array(
            'statistics' => $object->getStatistics()
        );
    }
}
