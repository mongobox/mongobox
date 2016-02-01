<?php

namespace Mongobox\Bundle\UsersBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserEditType
 * @package Mongobox\Bundle\UsersBundle\Form
 */
class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'username',
                'text',
                array(
                    'label' => 'Login',
                    'attr'  => array('size' => 15),
                )
            )
            ->add(
                'firstname',
                'text',
                array(
                    'label' => 'First name',
                    'attr'  => array('size' => 15),
                )
            )
            ->add(
                'lastname',
                'text',
                array(
                    'label' => 'Last name',
                    'attr'  => array('size' => 15),
                )
            )
            ->add(
                'email',
                'text',
                array(
                    'label' => 'Email',
                    'attr'  => array('size' => 40, 'placeholder' => 'Email'),
                )
            )
            ->add(
                'nsfw_mode',
                'checkbox',
                array(
                    'label'    => 'Mode NSFW',
                    'required' => false,
                )
            )
            ->add(
                'avatar',
                'file',
                array(
                    'label'      => 'Avatar',
                    'data_class' => null,
                    'required'   => false
                )
            );
    }

    public function getName()
    {
        return 'utilisateur_edition';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'intention' => $this->getName(),
        ));
    }
}
