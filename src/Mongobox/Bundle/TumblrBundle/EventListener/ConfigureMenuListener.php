<?php

namespace Mongobox\Bundle\TumblrBundle\EventListener;

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

        $tumblrMenu = $menu->addChild(
            'Tumblr',
            array(
                'uri'    => '#',
                'label'  => '<i class="fa fa-file-image-o fa-fw"></i> Tumblr <span class="fa arrow"></span>',
                'extras' => array('safe_label' => true)
            )
        );

        $tumblrMenu->setChildrenAttributes(array('class' => 'nav nav-second-level collapse'));

        $tumblrMenu->addChild('Nouvelle image', array('route' => 'tumblr_add'));
        $tumblrMenu->addChild('Listing', array('route' => 'tumblr'));
        $tumblrMenu->addChild('Classement', array('route' => 'tumblr_top'));

        $menuManipulator = new MenuManipulator();
        $menuManipulator->moveToPosition($tumblrMenu, 20);
    }

    /**
     * @param \Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent $event
     */
    public function onAdminMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $tumblrMenu = $menu->addChild(
            'Tumblr',
            array(
                'uri'    => '#',
                'label'  => '<i class="fa fa-file-image-o fa-fw"></i> Tumblr <span class="fa arrow"></span>',
                'extras' => array('safe_label' => true)
            )
        );

        $tumblrMenu->setChildrenAttributes(array('class' => 'nav nav-second-level collapse'));

        $tumblrMenu->addChild('Nouvelle image', array('route' => 'tumblr_add'));
        $tumblrMenu->addChild('Gestion des images', array('route' => 'admin_tumblr'));
        $tumblrMenu->addChild('Listing', array('route' => 'tumblr'));
        $tumblrMenu->addChild('Classement', array('route' => 'tumblr_top'));
        $tumblrMenu->addChild('Statistiques', array('route' => 'tumblr_stats'));

        $menuManipulator = new MenuManipulator();
        $menuManipulator->moveToPosition($tumblrMenu, 20);
    }
}
