<?php

namespace Mongobox\Bundle\JukeboxBundle\EventListener;

use Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    /**
     * @param \Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent $event
     */
    public function onMainMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menu->addChild('Gestion des vidéos', array('route' => 'videos'))->moveToPosition(10);
        $menu->addChild('Live', array('route' => 'live'))->moveToPosition(100);
    }
}
