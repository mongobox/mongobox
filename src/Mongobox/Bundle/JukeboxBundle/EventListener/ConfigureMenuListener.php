<?php

namespace Mongobox\Bundle\JukeboxBundle\EventListener;

use Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent;
use Knp\Menu\Util\MenuManipulator;

class ConfigureMenuListener
{
    /**
     * @param \Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent $event
     */
    public function onMainMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menuItemVideo = $menu->addChild('Liste des vidéos', array('route' => 'videos'));
        $menuItemLive = $menu->addChild('Live', array('route' => 'live'));

        $menuManipulator = new MenuManipulator();
        $menuManipulator->moveToFirstPosition($menuItemVideo);
        $menuManipulator->moveToLastPosition($menuItemLive);
    }

    /**
     * @param \Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent $event
     */
    public function onAdminMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $jukeboxMenu = $menu->addChild(
            'Jukebox',
            array(
                'route' => 'homepage',
                'attributes' => array('class' => 'dropdown'),
                'label' => 'Jukebox <b class="caret"></b>',
                'extras' => array('safe_label' => true)
            )
        );
        $jukeboxMenu->setLinkAttributes(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'));
        $jukeboxMenu->setChildrenAttributes(array('class' => 'dropdown-menu'));

        $menuManipulator = new MenuManipulator();
        $menuManipulator->moveToPosition($jukeboxMenu, 10);

        $jukeboxMenu->addChild('Nouvelle vidéo', array('route' => 'homepage'));
        $jukeboxMenu->addChild('Gestion des vidéos', array('route' => 'videos'));
        $jukeboxMenu->addChild('Gestion des tags', array('route' => 'homepage'));

        $menu->addChild('Live', array('route' => 'live'))->moveToLastPosition();
    }
}
