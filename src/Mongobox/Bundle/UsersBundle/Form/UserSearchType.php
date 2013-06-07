<?php

namespace Mongobox\Bundle\UsersBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', 'genemu_jqueryautocompleter_entity', array
                    (
                        'label' => 'Utilisateur',
                        'route_name' => 'ajax_user_search',
                        'class' => 'Mongobox\Bundle\UsersBundle\Entity\User',
                        'property' => 'id'
                    )
                 );
    }

    public function getName()
    {
        return 'ajax_user_search';
    }
}
