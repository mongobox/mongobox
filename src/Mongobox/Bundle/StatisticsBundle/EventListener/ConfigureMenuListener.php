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
                'uri'    => '#',
                'label'  => '<i class="fa fa-pie-chart fa-fw"></i> Stats <span class="fa arrow"></span>',
                'extras' => array('safe_label' => true)
            )
        );

        $statsMenu->setChildrenAttributes(array('class' => 'nav nav-second-level collapse'));

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
