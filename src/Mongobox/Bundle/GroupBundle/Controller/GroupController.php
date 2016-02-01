<?php

namespace Mongobox\Bundle\GroupBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Cookie;

use Mongobox\Bundle\GroupBundle\Entity\Group;
use Mongobox\Bundle\UsersBundle\Entity\User;
use Mongobox\Bundle\GroupBundle\Form\Type\GroupType;
use Mongobox\Bundle\UsersBundle\Form\Type\UserSearchType;

/**
 * @Route( "/group")
 */
class GroupController extends Controller
{
    /**
     * @Template()
     * @Route( "/", name="group_index")
     */
    public function indexAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $publicGroups = $em->getRepository('MongoboxGroupBundle:Group')->findBy(array('private' => 0));

        $privateGroups = $em->getRepository('MongoboxGroupBundle:Group')->findBy(
            array('private' => 1, "id" => $user->getGroupsIds())
        );


        return array(
            'publicGroups'  => $publicGroups,
            'privateGroups' => $privateGroups
        );
    }

    /**
     * @Template()
     * @Route( "/create", name="group_create")
     */
    public function groupCreateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //On créer le formulaire en utilisant un utilisateur vide
        $group = new Group();
        $form = $this->createForm(new GroupType(), $group);

        if ('POST' === $request->getMethod()) {
            $form->submit($request);
            if ($form->isValid()) {
                $secretKey = $this->get('mongobox_jukebox.live_configurator')->generateSecretKey();
                $group->setSecretKey($secretKey);
                $group->addRole('ROLE_USER');

                $em->persist($group);

                // On ajoute l'utilisateur courant dans le groupe
                $user->addGroup($group);
                $em->persist($user);

                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Groupe créé avec succès');

                return $this->redirect($this->generateUrl('homepage'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * Création d'un groupe en ajax lors de l'import des favoris
     * @Route("/ajax/group/create", name="ajax_group_create")
     */
    public function ajaxCreateGroupeAction()
    {
        $request = $this->get('request');

        $group = new Group();
        $form = $this->createForm(new GroupType(), $group);

        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $user = $this->getUser();

                $secretKey = $this->get('mongobox_jukebox.live_configurator')->generateSecretKey();
                $group->setSecretKey($secretKey);

                $em->persist($group);
                $group->getUsers()->add($user);
                $em->flush();

                $json = array(
                    "success"     => true,
                    "html_navbar" => $this->renderView(
                        'MongoboxGroupBundle:Navigation:groupNavbar.html.twig',
                        array('group' => $group)
                    ),
                    "message"     => "Le groupe a bien été crée",
                    "group_id"    => $group->getId(),
                    "group_text"  => $group->getName()
                );

                return new JsonResponse($json);
            }
        }

        return $this->render(
            'MongoboxGroupBundle:Group:groupAjaxCreate.html.twig',
            array(
                "form" => $form->createView()
            )
        );
    }

    /**
     * @Template()
     * @Route( "/edit/{id}", name="group_edit")
     * @ParamConverter("group", class="MongoboxGroupBundle:Group")
     */
    public function groupEditAction(Request $request, Group $group)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if ($user->isMemberFrom($group->getId())) {
            //On créer le formulaire en utilisant un utilisateur vide
            $form = $this->createForm(new GroupType(), $group);

            if ('POST' === $request->getMethod()) {
                $form->submit($request);
                if ($form->isValid()) {
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('success', 'Groupe édité avec succès');

                    return $this->redirect($this->generateUrl('homepage'));
                }
            }

            return array(
                'form'  => $form->createView(),
                'group' => $group
            );
        } else {
            $this->get('session')->getFlashBag()->add('notice', 'Vous n\'avez pas le droit de modifier ce groupe');

            return $this->redirect($this->generateUrl('homepage'));
        }
    }

    /**
     * @Template()
     * @Route( "/membres/{id}", name="group_membres")
     * @ParamConverter("group", class="MongoboxGroupBundle:Group")
     */
    public function groupMembresAction(Request $request, Group $group)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ($user->isMemberFrom($group->getId())) {
            return array(
                'group' => $group
            );
        } else {
            $this->get('session')->getFlashBag()->add('notice', 'Vous n\'avez pas le visualiser ce groupe');

            return $this->redirect($this->generateUrl('homepage'));
        }
    }

    /**
     * @Template()
     * @Route( "/inscription/{id}", name="group_inscription")
     * @ParamConverter("group", class="MongoboxGroupBundle:Group")
     */
    public function groupInscriptionAction(Request $request, Group $group)
    {
        if (!$group->getPrivate()) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->get('security.token_storage')->getToken()->getUser();

            $group->addUser($user);

            $em->persist($group);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'Inscription au groupe "' . $group->getName() . '" réussie.'
            );

            return $this->redirect($this->generateUrl('group_change', array('id_group' => $group->getId())));
        } else {
            $this->get('session')->getFlashBag()->add('notice', 'Vous ne pouvez pas vous inscrire à un groupe privé.');

            return $this->redirect($this->generateUrl('group_index'));
        }
    }

    /**
     * @Template()
     * @Route( "/invite/{id}", name="group_invite")
     * @ParamConverter("group", class="MongoboxGroupBundle:Group")
     */
    public function groupInviteAction(Request $request, Group $group)
    {
        if ($group->getPrivate()) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            if ($user->isMemberFrom($group->getId())) {
                $em = $this->getDoctrine()->getManager();

                // On créer le formulaire en utilisant un utilisateur vide
                $form = $this->createForm(new UserSearchType());

                if ('POST' === $request->getMethod()) {
                    $form->submit($request);
                    if ($form->isValid()) {
                        $user = $form->get('user')->getData();

                        if ($user instanceof User) {

                            if (!$group->hasUsersInvitation($user)) {
                                $group->addUsersInvitation($user);

                                $em->persist($group);
                                $em->flush();

                                $this->get('session')->getFlashBag()->add(
                                    'success',
                                    'Invitation à l\'utilisateur "' . $user->getUsername() . '" bien envoyée.'
                                );

                                return $this->redirect($this->generateUrl('homepage'));
                            } else {
                                $this->get('session')->getFlashBag()->add(
                                    'info',
                                    'Une invitation a déjà été envoyée à l\'utilisateur "' . $user->getUsername() . '".'
                                );

                                return $this->redirect(
                                    $this->generateUrl('group_invite', array('id' => $group->getId()))
                                );
                            }
                        }
                    }
                }

                return array(
                    'form'  => $form->createView(),
                    'group' => $group
                );
            }
        }
    }

    /**
     * @Template()
     * @Route( "/accept_invite/{group}/{user}", name="group_accept_invite")
     * @ParamConverter("group", class="MongoboxGroupBundle:Group")
     * @ParamConverter("user", class="MongoboxUsersBundle:User")
     */
    public function groupAcceptInviteAction(Request $request, Group $group, User $user)
    {
        $em = $this->getDoctrine()->getManager();

        // Delete user invitation
        $group->getUsersInvitations()->removeElement($user);

        // Add user in group
        $group->getUsers()->add($user);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'Inscription au groupe "' . $group->getName() . '" réussie.'
        );

        return $this->redirect($this->generateUrl('group_index'));
    }

    /**
     * @Route("/change/{id_group}", name="group_change")
     * @Template()
     * @author Coleyra
     * Change la groupe courante (via la liste de selection)
     */
    public function changeGroupAction(Request $request, $id_group)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if ($user->isMemberFrom($id_group)) {
            $session = $request->getSession();
            $session->set('id_group', $id_group);

            // On met l'id du groupe en cookie
            $response = new RedirectResponse($this->generateUrl('wall_index'));
            $response->headers->setCookie(new Cookie('id_group', $id_group));

            return $response;
        }
    }
}
