<?php

namespace Emakina\Bundle\LdapBundle\Ldap\Entity;

use Emakina\Component\ArrayAccess\AbstractArrayAccess as AbstractEntity;

class User extends AbstractEntity {
	
	public function configure() {
        $this->addProperty('city')
			->addProperty('department')
            ->addProperty('displayname')
            ->addProperty('dn')
            ->addProperty('givenname')
			->addProperty('mail')
            ->addProperty('manager')
            ->addProperty('memberof')
			->addProperty('parrain')
			->addProperty('photo')
			->addProperty('sn')
            ->addProperty('title')
			->addProperty('trigramme')
        ;
    }

	public function transform(array $data) {
        $this->set('displayname',$data['displayname'][0])
			 ->set('dn',$data['dn'])
             ->set('mail',$data['mail'][0])
			 ->set('memberof',$this->filterMemberOf($data['dn']))
        ;
		if(isset($data['sn'])) {
			$this->set('sn',$data['sn'][0]);
		}
		
		if(isset($data['givenname'])) {
			$this->set('givenname',$data['givenname'][0]);
		}
		
		if(isset($data['streetaddress'])) {
			$this->set('parrain',$this->filterParrain($data['streetaddress'][0]));
		}
		
		if(isset($data['samaccountname'])) {
			$this->set('trigramme',$data['samaccountname'][0]);
		}	
		
		if(isset($data['department'])) {
			$this->set('department',$data['department'][0]);
		}
		if(isset($data['manager'])) {
			$this->set('manager',$this->filterManager($data['manager'][0]));
		}
		if(isset($data['title'])) {
			$this->set('title',$data['title'][0]);
		}

		if(isset($data['l'])) {
			$this->set('city',$data['l'][0]);
		}
		if(isset($data['wwwhomepage'])) {
			$this->set('photo',$data['wwwhomepage'][0]);
		}

        return $this;
    }

	protected function filterParrain($data) {
		$result = '';
		if(preg_match('/Parrain:(.*)#/i',$data,$matches)) {
			$result = $matches[1];
		}
		
		return $result;
	}
	
	protected function filterMemberOf($memberof) {
		return array_slice(explode(',',str_replace(
			array('OU=','CN=','DC=', 'Digital ','WebBuilding','Brand Activation','Innovation','Operation'),
			array('','','','','Web Building','Interactive','Direction Technique','Direction des Opérations'),$memberof)),1,-3);
	}
	
	protected function filterManager($manager) {
		$data = array_slice(explode(',',str_replace(array('OU=','CN=','DC='),array(),$manager)),0,1);
		return $data[0];
	}	
}