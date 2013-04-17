<?php

namespace Mongobox\Bundle\JukeboxBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class VideoInfoType extends AbstractType
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
            ->add('tag', 'text', array(
                'label' => 'Tag à ajouter',
                'attr' => array('size' => 15),
				'mapped' => false,
				'required' => false,
            ))
            ->add('tags', 'hidden', array(
                'mapped' => false
            ));
        ;
    }

    public function getName()
    {
        return 'video_info';
    }
}
