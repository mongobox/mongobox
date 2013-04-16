<?php

namespace Mongobox\Bundle\JukeboxBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class VideoInfo extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('artist', 'text', array(
                'label' => 'Artiste',
                'attr' => array('size' => 40)
            ))
            ->add('songName', 'text', array(
                'label' => 'Nom de la chanson',
                'attr' => array('size' => 40)
            ))
				/*
            ->add('tags', 'text', array(
                'label' => 'Artiste',
                'attr' => array('size' => 40)
            ))
				 *
				 */
        ;
    }

    public function getName()
    {
        return 'video';
    }
}
