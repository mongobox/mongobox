<?php

namespace Mongobox\Bundle\UsersBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserOldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'text', array(
                'label' => 'Email',
                'attr' => array('size' => 40, 'placeholder' => 'Email')
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mongobox\Bundle\UsersBundle\Entity\UserOld'
        ));
    }

    public function getName()
    {
        return 'mongobox_bundle_usersbundle_useroldtype';
    }
}
