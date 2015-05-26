<?php

namespace Mongobox\Bundle\CoreBundle\Menu;

use Mongobox\Bundle\CoreBundle\Event\ConfigureMenuEvent;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class MainBuilder extends ContainerAware
{
    /**
     * @param FactoryInterface $factory
     * @return \Knp\Menu\ItemInterface
     */
    public function build(FactoryInterface $factory)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttributes(array('class' => 'nav navbar-nav'));

        $this->container
            ->get('event_dispatcher')
            ->dispatch(ConfigureMenuEvent::MAIN_MENU, new ConfigureMenuEvent($factory, $menu))
        ;

        return $menu;
    }
}
