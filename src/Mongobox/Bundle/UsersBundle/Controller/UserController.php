<?php

namespace Mongobox\Bundle\UsersBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Mongobox\Bundle\UsersBundle\Form\Type\UserEditType;
use Mongobox\Bundle\UsersBundle\Form\Type\UserEditPasswordType;

use Mongobox\Bundle\UsersBundle\Entity\User;

/**
 * @Route( "/user")
 *
 */
class UserController extends Controller
{

    /**
     * Fonction permettant de récupérer via JSON la liste des utilisateurs
     * @Route("/ajax_user_search", name="ajax_user_search")
     */
    public function ajaxUserSearchAction(Request $request)
    {
        $currentUser = $user = $this->get('security.token_storage')->getToken()->getUser();
        $value = $request->get('term');

        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('MongoboxUsersBundle:User')->findUser($value, $currentUser);

        $json = array();
        foreach ($users as $user) {
            $json[] = array(
                'label' => $user->getFirstName() . ' ' . $user->getLastName() . ' (' . $user->getUsername() . ')',
                'value' => $user->getFirstName() . ' ' . $user->getLastName() . ' (' . $user->getUsername() . ')',
                'id'    => $user->getId()
            );
        }

        return new Response(json_encode($json));
    }

    /**
     * Action to search tags for autocomplete field
     *
     * @Route("/users-ajax-get-user/{id_user}", name="users_get_user")
     * @Template()
     */
    public function getUserAction($id_user)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('MongoboxUsersBundle:User')->find($id_user);

        return new Response($user->getFirstName() . ' ' . $user->getLastName() . ' (' . $user->getUsername() . ')');
    }
}
