<?php

namespace Mongoeat\Bundle\RestaurantBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RestaurantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('phone')
            ->add('adresse')
            ->add('code')
            ->add('city')
            ->add('lat')
            ->add('lng')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mongoeat\Bundle\RestaurantBundle\Entity\Restaurant'
        ));
    }

    public function getName()
    {
        return 'mongoeat_bundle_restaurantbundle_restauranttype';
    }
}
