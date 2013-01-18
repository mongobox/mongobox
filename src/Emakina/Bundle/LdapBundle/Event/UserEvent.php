<?php

namespace Emakina\Bundle\LdapBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Emakina\Bundle\LdapBundle\Entity\User;

class UserEvent extends Event {
	protected $user;
	
	public function __construct(User $entity) {
		$this->user = $entity;
	}
	
	public function getUser() {
		return $this->user;
	}
	
	public function setUser(User $entity) {
		$this->user = $entity;
		return $this;
	}	
}