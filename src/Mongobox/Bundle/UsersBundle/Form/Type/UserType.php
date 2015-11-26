<?php

namespace Mongobox\Bundle\UsersBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserType
 * @package Mongobox\Bundle\UsersBundle\Form
 */
class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', 'text', array(
                'label' => 'First name',
                'attr' => array('size' => 15, 'placeholder' => 'First name')
            ))
            ->add('lastname', 'text', array(
                'label' => 'Last name',
                'attr' => array('size' => 15, 'placeholder' => 'Last name'),
            ))
           ->add('email', 'text', array(
                'label' => 'Email',
                'attr' => array('size' => 40, 'placeholder' => 'Email')
            ))
            ->add('login', 'text', array(
                'label' => 'Login',
                'attr' => array('size' => 15, 'placeholder' => 'Login')
            ))
            ->add('password', 'repeated', array(
                'type' => 'password',
                'first_name' => 'password',
                'second_name' => 'confirmation',
                'first_options' => array
                (
                    'label' => 'Password'
                )
            ))
        ;
    }

    public function getName()
    {
        return 'registration';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'intention' => $this->getName(),
        ));
    }
}
