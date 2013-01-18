<?php
namespace Emakina\Bundle\LdapBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

use Zend\Ldap\Ldap as ZendLdap;
use Zend\Ldap\Exception\LdapException;

class LdapUserProvider implements UserProviderInterface {
    protected $token = null ;	
	
	protected $ldapService;

	public function __construct(ZendLdap $ldapService) {
		$this->ldapService = $ldapService;
	}
	/**
     * Function for webservice to contact NBS clients database
     *
     * @param  string $username
     * @return WebserviceUser
     * @throws UsernameNotFoundException
     */
    public function loadUserByUsername($username)
    {
        try {
			$this->ldapService->bind($username,$this->token->getCredentials());	
			$canonicalName = $this->ldapService->getCanonicalAccountName($username, ZendLdap::ACCTNAME_FORM_DN);
			$data = $this->ldapService->getEntry($canonicalName,array(),true);
			
			$user = new LdapUser($username,$this->token->getCredentials());
			$user->transform($data);
			
			
        } catch (BadResponseException $e) {
        	throw new BadCredentialsException('Erreur lors de la tentative d\'authentification.');
        }

        return $user;
    }

    /**
     * Function to compare & check instances used
     *
     * @param UserInterface
     * @return WebserviceUser
     * @throws UnsupportedUserException
     */
    public function refreshUser(UserInterface $user)
    {

        if (!$user instanceof LdapUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $user;
    }

    /**
     * Function to get the comparaison between the custom user class & a class
     *
     * @param  array   $class
     * @return boolean
     */
    public function supportsClass($class)
    {
        return $class === 'Emakina\Bundle\LdapBundle\Security\User\LdapUserProvider';
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }
}