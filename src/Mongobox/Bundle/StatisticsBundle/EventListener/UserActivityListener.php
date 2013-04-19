<?php

namespace Mongobox\Bundle\StatisticsBundle\EventListener;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class UserActivityListener
{
    /**
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        //TODO
    }
}
