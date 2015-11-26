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
                'search',
                'text',
                array(
                    'label' => 'Lien Youtube ou Id de la vidéo',
                    'attr'  => array('size' => 15, 'class' => 'col-md-2 form-control search-query')
                )
            );
    }

    public function getName()
    {
        return 'Mongobox_bundle_jukeboxbundle_search_videos_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'intention' => $this->getName(),
        ));
    }
}
