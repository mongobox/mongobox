<?php

namespace Emakina\Bundle\LdapBundle\Ldap;

use Zend\Ldap\Ldap;
use Emakina\Bundle\LdapBundle\Ldap\Entity\User as LdapUser;
use Emakina\Bundle\LdapBundle\Ldap\Entity\OrganizationalUnit as LdapOrganizationalUnit;

class LdapManager {

    protected $ldap = null;

    public function __construct(Ldap $ldap) {
        $this->ldap = $ldap;
    }

    /**
     * @param $filter
     * @param $basedn
     * @param $scope
     * @return \Zend\Ldap\Collection
     */
    public function search($filter, $basedn, $scope) {
        $this->ldap->bind();
        $return =  $this->ldap->search($filter, $basedn, $scope);
        $result = array();
        foreach ($return as $item) {
			if($this->isExclude($item)) {
				continue;
			}
            $user = new LdapUser();
            $user->transform($item);
			$result[$user->get('displayname')] = $user;
        }
        ksort($result);
        return $result;
    }

	protected function isExclude(array $item ){
		if(strpos($item['dn'],',OU=Clients,') !== false) {
			return true;
		}
		if(strpos($item['dn'],',OU=BlueKiwi,') !== false) {
			return true;
		}
		if(isset($item['displayname']) && strpos($item['displayname'][0],'Server') !== false) {
			return true;
		}
		if(isset($item['cn']) && strpos($item['cn'][0],'merlinserver') !== false) {
			return true;
		}
		if(!isset($item['mail'])) {
			return true;
		}
		if(!isset($item['memberof'])) {
			return true;
		}
		
		return false;
	}
	
	public function save($dn, array $data) {
		$this->ldap->save($dn, $data );
	}
	
}