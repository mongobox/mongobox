<?php

namespace Mongobox\Bundle\JukeboxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReplaceVideo
 * @package Mongobox\Bundle\JukeboxBundle\Form\Type
 */
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
            ->add(
                'title',
                'text',
                array(
                    'label' => 'Nom de la vidéo',
                    'attr'  => array('size' => 15)
                )
            )
            ->add(
                'artist',
                'text',
                array(
                    'label' => 'Artiste',
                    'attr'  => array('size' => 15)
                )
            )
            ->add(
                'songName',
                'text',
                array(
                    'label' => 'Titre de la vidéo',
                    'attr'  => array('size' => 15)
                )
            )
            ->add(
                'lien',
                'text',
                array(
                    'label' => 'Id de la vidéo',
                    'attr'  => array('size' => 15)
                )
            )
            ->add(
                'tag',
                'autocomplete',
                array(
                    'class'    => 'Mongobox\Bundle\JukeboxBundle\Entity\VideoTag',
                    'label'    => 'Tags',
                    'attr'     => array(
                        'placeholder' => 'Ajouter des tags',
                    ),
                    'required' => false,
                    'mapped'   => false
                )
            )
            ->add(
                'tags',
                'hidden',
                array(
                    'mapped' => false
                )
            );
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
