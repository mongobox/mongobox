<?php
namespace Emakina\Bundle\LdapBundle\Security\Authentication\Provider;

use Emakina\Bundle\LdapBundle\Security\Authentication\Token\LdapToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class LdapAuthenticationProvider extends DaoAuthenticationProvider
{
    private $encoderFactory;
    private $userProvider;

    /**
     * Constructor.
     *
     * @param UserProviderInterface   $userProvider               An UserProviderInterface instance
     * @param UserCheckerInterface    $userChecker                An UserCheckerInterface instance
     * @param string                  $providerKey                The provider key
     * @param EncoderFactoryInterface $encoderFactory             An EncoderFactoryInterface instance
     * @param Boolean                 $hideUserNotFoundExceptions Whether to hide user not found exception or not
     */
    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey, EncoderFactoryInterface $encoderFactory, $hideUserNotFoundExceptions = true)
    {
        parent::__construct($userProvider,$userChecker, $providerKey, $encoderFactory,$hideUserNotFoundExceptions);

        $this->encoderFactory = $encoderFactory;
        $this->userProvider = $userProvider;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof LdapToken;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        $currentUser = $token->getUser();
	
        if ($currentUser instanceof UserInterface) {
            if ($currentUser->getPassword() !== $user->getPassword()) {
                throw new BadCredentialsException('The credentials were changed from another session.');
            }
        } else {
            if ("" === ($presentedPassword = $token->getCredentials())) {
                throw new BadCredentialsException('The presented password cannot be empty.');
            }

            if (!$this->encoderFactory->getEncoder($user)->isPasswordValid($user->getPassword(), $presentedPassword, $user->getSalt())) {
                throw new BadCredentialsException('The presented password is invalid.');
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    protected function retrieveUser($username, UsernamePasswordToken $token)
    {
        $user = $token->getUser();
		
        if ($user instanceof UserInterface) {

            return $user;
        }

        try {
            $user = $this->userProvider->setToken($token)->loadUserByUsername($username);
				
            if (!$user instanceof UserInterface) {
                throw new AuthenticationServiceException('The user provider must return a UserInterface object.');
            }
				
            return $user;
        } catch (UsernameNotFoundException $notFound) {
            throw $notFound;
        } catch (\Exception $repositoryProblem) {
            throw new AuthenticationServiceException($repositoryProblem->getMessage(), $token, 0, $repositoryProblem);
        }
    }
}
