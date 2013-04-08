<?php

namespace Mongobox\Bundle\CoreBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Admin Default controller.
 *
 * @Route("/admin")
 */
class DefaultController extends Controller
{
    /**
     * @Route("",name="admin_index")
     * @Template()
     */
    public function indexAction()
    {
        //var_dump(__METHOD__);exit;
        return array();
    }
}
