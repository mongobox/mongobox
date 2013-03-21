<?php

namespace Mongobox\Bundle\StatisticsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Mongobox\Bundle\StatisticsBundle\Statistics;

class TumblrController extends Controller
{
    /**
     * @Route("/tumblr/stats", name="tumblr_stats")
     * @Template()
     */
    public function indexAction()
    {
        $dm     = $this->getDoctrine()->getManager();
        $object = new Statistics\Tumblr($dm);

        return array(
            'statistics' => $object->getStatistics()
        );
    }
}
