<?php

namespace Mongoeat\Bundle\VoteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DecisionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date')
            ->add('group')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mongoeat\Bundle\VoteBundle\Entity\Decision'
        ));
    }

    public function getName()
    {
        return 'mongoeat_bundle_votebundle_decisiontype';
    }
}
