<?php

namespace Mongobox\Bundle\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class MenuBuilder
{
    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $factory;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param Request $request
     * @return \Knp\Menu\ItemInterface
     */
    public function createMainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');

        $menu->setChildrenAttributes(array('class' => 'nav'));

        $menu->addChild('Gestion des vidéos', array('route' => 'videos'));

        $tumblrMenu = $menu->addChild('Tumblr', array('route' => 'homepage', 'attributes' => array('class' => 'dropdown')));
        $tumblrMenu->setLinkAttributes(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'));
        $tumblrMenu->setChildrenAttributes(array('class' => 'dropdown-menu'));

        $tumblrMenu->addChild('Nouvelle image', array('route' => 'mongo_pute_add'));
        $tumblrMenu->addChild('Listing', array('route' => 'mongo_pute'));
        $tumblrMenu->addChild('Classement', array('route' => 'tumblr_top'));
        $tumblrMenu->addChild('Statistiques', array('route' => 'tumblr_stats'));

        $menu->addChild('Live', array('route' => 'live'));

        return $menu;
    }
}
