<?php

namespace Mongobox\Bundle\JukeboxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SearchVideosType
 * @package Mongobox\Bundle\JukeboxBundle\Form\Type
 */
class SearchVideosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'query',
                'search',
                array(
                    'label' => false,
                    'attr'  => array(
                        'size'        => 15,
                        'class'       => 'search-query',
                        'placeholder' => "Search Video..."
                    )
                )
            );
    }

    public function getName()
    {
        return null;
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
