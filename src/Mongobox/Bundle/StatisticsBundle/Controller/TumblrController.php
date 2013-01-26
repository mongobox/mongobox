<?php

namespace Mongobox\Bundle\StatisticsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Mongobox\Bundle\StatisticsBundle\Statistics;

class TumblrController extends Controller
{
    /**
     * @Route("/tumblr/stats")
     * @Template()
     */
    public function indexAction()
    {
        $dm     = $this->getDoctrine()->getManager();
        $object = new Statistics\Tumblr($dm);

        $statistics = $object->getStatistics();

        return array(
            'timeline'  => $statistics['timeline']
        );
    }
}
