<?php

namespace Mongobox\Bundle\TumblrBundle\EventListener;

use Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    /**
     * @param \Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent $event
     */
    public function onMainMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $tumblrMenu = $menu->addChild('Tumblr', array('route' => 'homepage', 'attributes' => array('class' => 'dropdown')));
        $tumblrMenu->setLinkAttributes(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'));
        $tumblrMenu->setChildrenAttributes(array('class' => 'dropdown-menu'));

        $tumblrMenu->addChild('Nouvelle image', array('route' => 'mongo_pute_add'));
        $tumblrMenu->addChild('Listing', array('route' => 'mongo_pute'));
        $tumblrMenu->addChild('Classement', array('route' => 'tumblr_top'));

        $tumblrMenu->moveToPosition(20);
    }
}
