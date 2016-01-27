<?php

namespace Mongobox\Bundle\StatisticsBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class UserActivityListener
{
    private $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     *
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Retrieve the currently logged user
     *
     * @return \Mongobox\Bundle\UsersBundle\Entity\User | boolean
     */
    protected function getCurrentUser()
    {
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('ROLE_USER')) {
            return $this->container->get('security.token_storage')->getToken()->getUser();
        }

        return false;
    }

    /**
     * Update the date of last activity for the current user
     *
     * @param PostResponseEvent $event
     *
     * @return void
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($request->isXmlHttpRequest()) {
            return;
        }

        if ($currentUser = $this->getCurrentUser()) {
            $userActivity = $this->container->get('mongobox_statistics.user_activity');

            $userActivity->updateLastHeartBeat($currentUser);
            $userActivity->updateConnectionsPeak();
        }
    }
}
