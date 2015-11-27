<?php

namespace Mongobox\Bundle\JukeboxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VideoSearchType
 * @package Mongobox\Bundle\JukeboxBundle\Form
 */
class VideoSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'search',
                'text',
                array(
                    'label'  => 'Rechercher une vidéo dans la Mongobox ou sur Youtube',
                    'attr'   => array(
                        'size'        => 15,
                        'placeholder' => 'Rechercher une vidéo dans la Mongobox ou sur Youtube',
                        'class'       => 'form-control'
                    ),
                    'mapped' => false
                )
            );
    }

    public function getName()
    {
        return 'video_search';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'intention' => $this->getName(),
        ));
    }
}
