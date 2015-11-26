<?php

namespace Mongobox\Bundle\JukeboxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VideoInfoType
 * @package Mongobox\Bundle\JukeboxBundle\Form
 */
class VideoInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'artist',
                'text',
                array(
                    'label' => 'Artiste',
                    'attr'  => array('size' => 40)
                )
            )
            ->add(
                'songName',
                'text',
                array(
                    'label' => 'Nom de la vidéo',
                    'attr'  => array('size' => 40)
                )
            )
            ->add(
                'tag',
                'genemu_jqueryautocompleter_entity',
                array(
                    'route_name' => 'video_tags_ajax_autocomplete',
                    'class'      => 'Mongobox\Bundle\JukeboxBundle\Entity\VideoTag',
                    'property'   => 'name',
                    'label'      => 'Tags',
                    'attr'       => array(
                        'placeholder' => 'Ajouter des tags',
                    ),
                    'required'   => false,
                    'mapped'     => false
                )
            )
            ->add(
                'tags',
                'hidden',
                array(
                    'mapped' => false
                )
            );;
    }

    public function getName()
    {
        return 'video_info';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'intention' => $this->getName(),
        ));
    }
}
