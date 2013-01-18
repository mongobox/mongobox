<?php

namespace Emakina\Bundle\LdapBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('dn','hidden')
            ->add('trigramme','text',array('read_only' => true))
            ->add('firstname','text',array(
            	'label'		=> 'Prénom',
            ))
			->add('lastname','text',array(
            	'label'		=> 'Nom',
            ))
            ->add('email')
            ->add('photo')
        	->add('is_enabled', 'choice', array(
	            'choices'   => array('0' => 'Non', '1' => 'Oui'),
	            'required'  => true,
	            'label'	=> 'Fait partie de la société ?'
	        ))          
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Emakina\Bundle\LdapBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'emakina_bundle_ldapbundle_usertype';
    }
}
