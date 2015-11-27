<?php

namespace Mongobox\Bundle\AuthenticationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SecuredController extends Controller
{
    /**
     * @Route("/login", name="_login")
     * @Template()
     */
    public function loginAction()
    {
        if ($this->get('request')->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $this->get('request')->attributes->get(Security::AUTHENTICATION_ERROR);
        } else {
            $error = $this->get('request')->getSession()->get(Security::AUTHENTICATION_ERROR);
        }

        return array(
            'last_username' => $this->get('request')->getSession()->get(Security::LAST_USERNAME),
            'error'         => $error,
        );
    }
}
