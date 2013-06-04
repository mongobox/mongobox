<?php
namespace Mongobox\Bundle\UsersBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormError;

class UserHandler
{
    protected $form;
    protected $request;
    protected $em;

    public function __construct(Form $form, Request $request, EntityManager $em)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->em      = $em;
    }

    public function process()
    {
        //Vérification que l'email n'existe pas déjà si email personnel
        $users = $this->em->getRepository('MongoboxUsersBundle:User')->findByEmail($this->form->get('email')->getData());
        if ($users) {
            $this->form->get('email')->addError(new FormError('This email already exists '));
        }
    }

    public function onSuccess()
    {
    }
}
