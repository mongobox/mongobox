<?php

namespace Mongobox\Bundle\StatisticsBundle\EventListener;

use Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    /**
     * @param \Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent $event
     */
    public function onMainMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $tumblrMenu = $menu->getChild('Tumblr');
        $tumblrMenu->addChild('Statistiques', array('route' => 'tumblr_stats'));
    }
}
