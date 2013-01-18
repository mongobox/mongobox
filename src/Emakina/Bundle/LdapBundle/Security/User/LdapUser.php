<?php
namespace Emakina\Bundle\LdapBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Emakina\Component\ArrayAccess\AbstractArrayAccess as AbstractEntity;

class LdapUser extends AbstractEntity 
	implements UserInterface {

	const DN_ROLE_ADMIN = 'CN=Ika Administrateur,CN=Users,DC=groupereflect,DC=net';
	
	protected $password;
	
	protected $username;
	
	protected $roles = array();
	
	public function __construct($username,$password) {
		$this->username = $username;
		$this->password = $password;
		
		parent::__construct();
	}
	
	public function configure() {
        $this->addProperty('city')
			->addProperty('company')
            ->addProperty('department')
            ->addProperty('displayname')
            ->addProperty('dn')
            ->addProperty('mail')
            ->addProperty('manager')
            ->addProperty('memberof')
            ->addProperty('parrain')
			->addProperty('photo')
            ->addProperty('title')
			->addProperty('user')
        ;
    }

    public function transform(array $data) {
        $this
        	 //->set('city',$data['l'][0])
			 //->set('company',$data['company'][0])
             //->set('department',$data['department'][0])
             ->set('displayname',$data['displayname'][0])
			 ->set('dn',$data['dn'])
             ->set('mail',$data['mail'][0])
             ->set('manager',$this->filterManager($data['manager'][0]))
			 ->set('memberof',$this->filterMemberOf($data['dn']))
			// ->set('parrain',$this->filterParrain($data['streetaddress'][0]))
			 ->set('photo',$data['wwwhomepage'][0])
			 ->set('title',$data['title'][0])
        ;

		if(is_array($data['memberof'])) {
			if(in_array(self::DN_ROLE_ADMIN,$data['memberof'])) {
				$this->setRoles(array('ROLE_ADMIN'));
			} else { 
				$this->setRoles(array('ROLE_USER'));
			}
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
		return array_slice(explode(',',str_replace(array('OU=','CN=','DC='),array(),$memberof)),1,-3);
	}
	
	protected function filterManager($manager) {
		$data = array_slice(explode(',',str_replace(array('OU=','CN=','DC='),array(),$manager)),0,1);
		return $data[0];
	}
	
	/**
	 * @return the $username
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * @param string $username
	 */
	public function setUsername($username)
	{
		$this->username = $username;
		return $this;
	}
	
	/**
	 * @return the $password
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 */
	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}

	/**
	 * @return the $roles
	 */
	public function getRoles()
	{
		return $this->roles;
	}

	/**
	 * @param array $roles
	 */
	public function setRoles($roles)
	{
		$this->roles = $roles;
		return $this;
	}	
	
	/**
	 * @return the $salt
	 */
	public function getSalt()
	{
		return '';
	}

	/**
	 * @param string $salt
	 */
	public function setSalt($salt)
	{
		return $this;
	}
	

	/**
	 * (non-PHPdoc)
	 * @see \Symfony\Component\Security\Core\User\UserInterface::eraseCredentials()
	 */
	public function eraseCredentials()
	{
		//$this->setPassword(null);
	}
}