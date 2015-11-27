<?php

namespace Mongoeat\Bundle\RestaurantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RestaurantType
 * @package Mongoeat\Bundle\RestaurantBundle\Form\Type
 */
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mongoeat\Bundle\RestaurantBundle\Entity\Restaurant',
            'intention' => $this->getName()
        ));
    }

    public function getName()
    {
        return 'mongoeat_bundle_restaurantbundle_restauranttype';
    }
}
