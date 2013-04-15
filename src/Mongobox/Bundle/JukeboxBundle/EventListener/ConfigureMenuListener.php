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

        $menu->addChild('Gestion des vidéos', array('route' => 'videos'))->moveToFirstPosition();
        $menu->addChild('Live', array('route' => 'live'))->moveToLastPosition();
    }

    /**
     * @param \Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent $event
     */
    public function onAdminMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $jukeboxMenu = $menu->addChild('Jukebox', array('route' => 'homepage', 'attributes' => array('class' => 'dropdown')));
        $jukeboxMenu->setLinkAttributes(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'));
        $jukeboxMenu->setChildrenAttributes(array('class' => 'dropdown-menu'));
        $jukeboxMenu->moveToPosition(10);

        $jukeboxMenu->addChild('Nouvelle vidéo', array('route' => 'homepage'));
        $jukeboxMenu->addChild('Gestion des vidéos', array('route' => 'videos'));
        $jukeboxMenu->addChild('Gestion des tags', array('route' => 'homepage'));

        $menu->addChild('Live', array('route' => 'live'))->moveToLastPosition();
    }
}
