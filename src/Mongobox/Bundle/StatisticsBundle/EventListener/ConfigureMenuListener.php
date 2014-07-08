<?php

namespace Mongobox\Bundle\StatisticsBundle\EventListener;

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


        $statsMenu = $menu->addChild(
            'Jukebox',
            array(
                'route'         => 'homepage',
                'attributes'    => array('class' => 'dropdown'),
                'label'         => 'Stats <b class="caret"></b>',
                'extras'        => array('safe_label' => true)
            )
        );

        $statsMenu->setLinkAttributes(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'));
        $statsMenu->setChildrenAttributes(array('class' => 'dropdown-menu'));

        $menuManipulator = new MenuManipulator();
        $menuManipulator->moveToPosition($statsMenu,2);

        $statsMenu->addChild('Jukebox', array('route' => 'jukebox_stats'));
        $statsMenu->addChild('Tumblr', array('route' => 'tumblr_stats'));
    }

    /**
     * @param \Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent $event
     */
    public function onAdminMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
    }
}
