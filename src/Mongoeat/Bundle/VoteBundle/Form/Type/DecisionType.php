<?php

namespace Mongoeat\Bundle\VoteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DecisionType
 * @package Mongoeat\Bundle\VoteBundle\Form\Type
 */
class DecisionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date')
            ->add('group')
        ;
    }

    public function configureOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mongoeat\Bundle\VoteBundle\Entity\Decision',
            'intention' => $this->getName()
        ));
    }

    public function getName()
    {
        return 'mongoeat_bundle_votebundle_decisiontype';
    }
}
