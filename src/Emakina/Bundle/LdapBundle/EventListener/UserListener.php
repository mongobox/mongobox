<?php

namespace Emakina\Bundle\LdapBundle\EventListener;

use  Emakina\Bundle\LdapBundle\Event\UserEvent;

class UserListener {
	protected $ldapManager;
	
	public function __construct($ldapManager) {
		$this->ldapManager = $ldapManager;
	}

	public function onUserPostSave(UserEvent $event) {
		$entity = $event->getUser();
		
		$data = array(
			'title' 		=> $entity->getTitle(),
			'streetaddress'	=> "Parrain:{$entity->getParrain()}#\n",
			'l'				=> $entity->getCity(),
			//'manager'		=> $entity->getManager()->getDn(),
		); 
		
		$this->ldapManager->save($entity->getDn(),$data);
		
	}
}