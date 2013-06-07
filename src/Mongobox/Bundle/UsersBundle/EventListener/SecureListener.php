<?php

namespace Mongobox\Bundle\UsersBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class SecureListener
{
    protected $security;
    protected $em;
    protected $router;
    protected $dispatcher;

    public function __construct(SecurityContext $s,EntityManager $em, RouterInterface $router, EventDispatcher $dispatcher)
    {
        $this->security = $s;
        $this->em = $em;
        $this->router = $router;
        $this->dispatcher = $dispatcher;

    }
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->security->getToken()->getUser();
            $entity = $this->em->getRepository('MongoboxUsersBundle:UserOld')->findByLastId($user->getId());

            if (empty($entity)) {
                $this->dispatcher->addListener(KernelEvents::RESPONSE, array($this, 'redirectUserToProfilePage'));
            }
        }
    }
    public function redirectUserToProfilePage(FilterResponseEvent $event)
    {
        // on effectue la redirection
        $response = new RedirectResponse($this->router->generate('userold_new'));
        $event->setResponse($response);
    }
}
