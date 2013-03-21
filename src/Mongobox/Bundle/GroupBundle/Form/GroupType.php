<?php

namespace Mongobox\Bundle\GroupBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
            	'label' => 'Nom',
            	'attr' => array('size' => 15, 'placeholder' => 'Nom')
            ))
            ->add('private', 'checkbox', array(
            	'label' => 'Privé'
            ))
        ;
	}

    public function getName()
    {
        return 'group_create';
    }
}
