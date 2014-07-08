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
                'route'      => 'homepage',
                'attributes' => array('class' => 'dropdown'),
                'label'      => 'Tumblr <b class="caret"></b>',
                'extras'     => array('safe_label' => true)
            )
        );
        $tumblrMenu->setLinkAttributes(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'));
        $tumblrMenu->setChildrenAttributes(array('class' => 'dropdown-menu'));

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
                'route'      => 'homepage',
                'attributes' => array('class' => 'dropdown'),
                'label'      => 'Tumblr <b class="caret"></b>',
                'extras'     => array('safe_label' => true)
            )
        );
        $tumblrMenu->setLinkAttributes(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'));
        $tumblrMenu->setChildrenAttributes(array('class' => 'dropdown-menu'));

        $tumblrMenu->addChild('Nouvelle image', array('route' => 'tumblr_add'));
        $tumblrMenu->addChild('Gestion des images', array('route' => 'admin_tumblr'));
        $tumblrMenu->addChild('Listing', array('route' => 'tumblr'));
        $tumblrMenu->addChild('Classement', array('route' => 'tumblr_top'));
        $tumblrMenu->addChild('Statistiques', array('route' => 'tumblr_stats'));

        $menuManipulator = new MenuManipulator();
        $menuManipulator->moveToPosition($tumblrMenu, 20);
    }
}
