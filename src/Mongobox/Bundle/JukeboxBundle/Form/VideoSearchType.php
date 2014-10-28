<?php

namespace Mongobox\Bundle\JukeboxBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class VideoSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', 'text', array(
                'label' => 'Rechercher une vidéo dans la Mongobox ou sur Youtube',
                'attr' => array(
                    'size' => 15,
                    'placeholder' => 'Rechercher une vidéo dans la Mongobox ou sur Youtube',
                    'class' => 'form-control'
                ),
				'mapped' => false
            ))
        ;
    }

    public function getName()
    {
        return 'video_search';
    }
}
