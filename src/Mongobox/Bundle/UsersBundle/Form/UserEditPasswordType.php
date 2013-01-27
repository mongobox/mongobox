<?php

namespace Mongobox\Bundle\UsersBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserEditPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('old_password', 'password', array(
                'label' => 'Ancien mot de passe',
                'property_path' => false,
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
                'property_path' => false,
				'attr' => array('data-help' => 'Veuillez renseigner un nouveau mot de passe pour pouvoir accéder à votre espace. Tout caractère sauf l\'espace.')
            ))   
        ;
    }

    public function getName()
    {
        return 'utilisateur_edition_mot_de_passe';
    }
}
