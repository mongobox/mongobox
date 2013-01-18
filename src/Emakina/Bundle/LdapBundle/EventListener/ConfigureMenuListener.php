<?php

namespace Emakina\Bundle\LdapBundle\EventListener;

use Emakina\Ika\Bundle\CoreBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    /**
     * @param \Emakina\Ika\Bundle\CoreBundle\Event\ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();  
		$menu->addChild('Utilisateurs', array('route' => 'admin_user','extras'=>array('icon' => 'user')));
		$menu->addChild('Unités', array('route' => 'admin_unit','extras'=>array('icon' => 'th-list')));

    }
}
