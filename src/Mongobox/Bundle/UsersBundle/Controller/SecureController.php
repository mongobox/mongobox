<?php

namespace Mongobox\Bundle\UsersBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

use Mongobox\Bundle\UsersBundle\Form\UserType;
use Mongobox\Bundle\UsersBundle\Entity\User;

class SecureController extends Controller
{
    /**
     * This action handles the registration.
     *
     * @Route("/registration", name="registration")
     * @Method({ "GET", "POST" })
     * @Template()
     */
    public function registrationAction(Request $request)
    {
        $status = 1;
        //on verifie si on est pas deja connecté
        $securityContext = $this->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        //On créer le formulaire en utilisant un utilisateur vide
        $user = new User();
        $form = $this->createForm(new UserType(), $user);

        if ('POST' === $request->getMethod()) {
            $em = $this->getDoctrine()->getManager();
            $form->submit($request);

            if ($form->isValid()) {
                $user->setActif($status);

                //On encode le mot de passe
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $user->encodePassword($encoder);

                $em->persist($user);
                $em->flush();

                $request->getSession()->getFlashBag()->add('success', 'Your account as been created with success');

                return $this->redirect($this->generateUrl('wall_index'));
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/login", name="login")
     * @Template()
     * @Method({ "GET", "POST" })
     */
    public function loginAction(Request $request)
    {
        //on verifie si on est pas deja connecte
        $securityContext = $this->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect($this->generateUrl('wall_index'));
        }

        $session = $request->getSession();

        $error = $session->get(Security::AUTHENTICATION_ERROR);
        $session->remove(Security::AUTHENTICATION_ERROR);

        return array(
            'error'         => $error,
            'last_username' => $session->get(Security::LAST_USERNAME)
        );
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
    }
}
