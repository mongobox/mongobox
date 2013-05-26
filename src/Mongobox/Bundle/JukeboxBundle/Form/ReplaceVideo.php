<?php

namespace Mongobox\Bundle\JukeboxBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ReplaceVideo extends AbstractType
{
    public function getName()
    {
        return 'Mongobox_bundle_jukeboxbundle_live_replace_video';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('title', 'text', array(
                'label' => 'Nom de la vidéo',
                'attr'  => array('size' => 15)
            ))
            ->add('artist', 'text', array(
                'label' => 'Artiste',
                'attr'  => array('size' => 15)
            ))
            ->add('songName', 'text', array(
                'label' => 'Titre de la vidéo',
                'attr'  => array('size' => 15)
            ))
            ->add('lien', 'text', array(
                'label' => 'Id de la vidéo',
                'attr'  => array('size' => 15)
            ))
        ;
    }
}
