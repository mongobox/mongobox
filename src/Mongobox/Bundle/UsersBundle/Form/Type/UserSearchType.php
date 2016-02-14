<?php

namespace Mongobox\Bundle\UsersBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserSearchType
 *
 * @package Mongobox\Bundle\UsersBundle\Form
 */
class UserSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'user',
                'autocomplete',
                array(
                    'class'    => 'MongoboxUsersBundle:User',
                    'label'    => 'Utilisateur',
                    'attr'     => array(
                        'placeholder' => 'Rechercher un utilisateur'
                    ),
                    'required' => false,
                    'mapped'   => false
                )
            );
    }

    public function getName()
    {
        return 'ajax_user_search';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'intention' => $this->getName(),
            )
        );
    }
}
