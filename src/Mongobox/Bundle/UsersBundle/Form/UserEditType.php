<?php

namespace Mongobox\Bundle\UsersBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login', 'text', array(
            	'label' => 'Login',
            	'attr' => array('size' => 15),
            ))
            ->add('firstname', 'text', array(
            	'label' => 'First name',
            	'attr' => array('size' => 15),
            ))
            ->add('lastname', 'text', array(
            	'label' => 'Last name',
            	'attr' => array('size' => 15),
            ))
            ->add('avatar', 'file', array('label' => ' ', 'required' => false))
        ;
    }

    public function getName()
    {
        return 'user_edition';
    }
}
