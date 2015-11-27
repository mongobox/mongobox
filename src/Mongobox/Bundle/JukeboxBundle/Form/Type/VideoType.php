<?php

namespace Mongobox\Bundle\JukeboxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VideoType
 * @package Mongobox\Bundle\JukeboxBundle\Form
 */
class VideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'lien',
                'text',
                array(
                    'label' => 'Lien Youtube ou Id de la vidéo',
                    'attr'  => array('size'        => 15,
                                     'placeholder' => 'Lien vers la vidéo Youtube',
                                     'class'       => 'form-control'
                    )
                )
            );
    }

    public function getName()
    {
        return 'video';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'intention' => $this->getName(),
        ));
    }
}
