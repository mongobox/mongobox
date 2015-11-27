<?php

namespace Mongoeat\Bundle\VoteBundle\EventListener;

use Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent;
use Knp\Menu\Util\MenuManipulator;

class ConfigureMenuListener
{
    /**
     * @param \Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent $event
     */
    public function onEatMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menuItemMongoEat = $menu->addChild('MongoEat', array('route' => 'decision'));
        $menuItemBack = $menu->addChild(
            'Retour a la mongobox',
            array('route' => 'wall_index', 'attributes' => array('class' => 'pull-right'))
        );

        $menuManipulator = new MenuManipulator();
        $menuManipulator->moveToFirstPosition($menuItemMongoEat);
        $menuManipulator->moveToLastPosition($menuItemBack);
    }

    /**
     * @param \Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent $event
     */
    public function onMainMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menu->addChild(
            'MongoEat',
            array(
                'route'  => 'decision',
                'label'  => '<i class="fa fa-cutlery fa-fw"></i> MongoEat',
                'extras' => array('safe_label' => true)
            )
        );
    }
}
