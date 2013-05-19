<?php

namespace Mongoeat\Bundle\VoteBundle\EventListener;

use Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    /**
     * @param \Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent $event
     */
    public function onEatMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menu->addChild('MongoEat', array('route' => 'decision'))->moveToFirstPosition();
        $menu->addChild('Retour a la mongobox', array('route' => 'wall_index', 'attributes' => array('class' => 'pull-right')))->moveToLastPosition();
    }

}
