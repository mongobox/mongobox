<?php

namespace Mongobox\Bundle\JukeboxBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class VideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lien', 'text', array(
                'label' => 'Lien Youtube ou Id de la vidéo',
                'attr' => array('size' => 15, 'placeholder' => 'Lien vers la vidéo Youtube','class' => 'form-control')
            ))
        ;
    }

    public function getName()
    {
        return 'video';
    }
}
