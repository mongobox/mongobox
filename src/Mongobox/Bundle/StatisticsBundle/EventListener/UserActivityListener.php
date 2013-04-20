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
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function getCurrentUser()
    {
        $securityContext = $this->container->get('security.context');
        if ($token = $securityContext->getToken()) {
            return $token->getUser();
        }

        return false;
    }

    /**
     * Updates the date of last activity for the current user
     *
     * @param PostResponseEvent $event
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
        }
    }
}
