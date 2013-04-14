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

    public function createAdminMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');

        $menu->setChildrenAttributes(array('class' => 'nav'));

        $menu->addChild('Dashboard', array('route' => 'admin_index'));

        $jukeboxMenu = $menu->addChild('Jukebox', array('route' => 'homepage', 'attributes' => array('class' => 'dropdown')));
        $jukeboxMenu->setLinkAttributes(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'));
        $jukeboxMenu->setChildrenAttributes(array('class' => 'dropdown-menu'));

        $jukeboxMenu->addChild('Nouvelle vidéo', array('route' => 'homepage'));
        $jukeboxMenu->addChild('Gestion des vidéos', array('route' => 'videos'));
        $jukeboxMenu->addChild('Gestion des tags', array('route' => 'homepage'));

        $tumblrMenu = $menu->addChild('Tumblr', array('route' => 'homepage', 'attributes' => array('class' => 'dropdown')));
        $tumblrMenu->setLinkAttributes(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'));
        $tumblrMenu->setChildrenAttributes(array('class' => 'dropdown-menu'));

        $tumblrMenu->addChild('Nouvelle image', array('route' => 'mongo_pute_add'));
        $tumblrMenu->addChild('Gestion des images', array('route' => 'admin_tumblr'));
        $tumblrMenu->addChild('Listing', array('route' => 'mongo_pute'));
        $tumblrMenu->addChild('Classement', array('route' => 'tumblr_top'));
        $tumblrMenu->addChild('Statistiques', array('route' => 'tumblr_stats'));

        $menu->addChild('Membres', array('route' => 'homepage'));

        $menu->addChild('Live', array('route' => 'live'));

        return $menu;
    }
}
