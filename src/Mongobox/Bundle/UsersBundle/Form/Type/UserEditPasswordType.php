<?php

namespace Mongobox\Bundle\UsersBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserEditPasswordType
 * @package Mongobox\Bundle\UsersBundle\Form
 */
class UserEditPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('old_password', 'password', array(
                'label' => 'Ancien mot de passe',
                'mapped' => false,
                'attr' => array('data-help' => 'Veuillez renseigner votre ancien mot de passe.')
            ))
            ->add('new_password', 'repeated', array(
                'type' => 'password',
                'first_name' => 'password',
                'second_name' => 'confirmation',
                'first_options' => array
                (
                    'label' => 'Mot de passe'
                ),
                'mapped' => false,
                'attr' => array('data-help' => 'Veuillez renseigner un nouveau mot de passe pour pouvoir accéder à votre espace. Tout caractère sauf l\'espace.')
            ))
        ;
    }

    public function getName()
    {
        return 'utilisateur_edition_mot_de_passe';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'intention' => $this->getName(),
        ));
    }
}
