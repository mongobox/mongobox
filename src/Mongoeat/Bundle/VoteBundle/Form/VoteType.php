<?php

namespace Mongoeat\Bundle\VoteBundle\Form;

use Doctrine\ORM\EntityRepository;
use Mongoeat\Bundle\RestaurantBundle\Entity\RestaurantRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VoteType extends AbstractType
{
    public $city;

    public function __construct($city)
    {
        $this->city=$city;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $city = $this->city;
        $builder
            ->add('restaurant','entity',array(
                'class' => 'MongoeatRestaurantBundle:Restaurant',
                'query_builder' => function(RestaurantRepository $er) use ($city) {
                    return $er->findSortVotes($city);
                },
                'multiple'	=> false,
                'expanded'	=> true,
            ))
        ;
    }


    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mongoeat\Bundle\VoteBundle\Entity\Vote'
        ));
    }

    public function getName()
    {
        return 'mongoeat_bundle_votebundle_votetype';
    }
}
