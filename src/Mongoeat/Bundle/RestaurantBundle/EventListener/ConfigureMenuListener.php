<?php

namespace Mongoeat\Bundle\RestaurantBundle\EventListener;

use Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    /**
     * @param \Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent $event
     */
    public function onEatMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menu->addChild('Restaurant', array('route' => 'restaurant'));

    }

}
