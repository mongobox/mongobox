<?php

namespace Mongobox\Bundle\JukeboxBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchVideosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', 'text',array(
                'label' => 'Lien Youtube ou Id de la vidéo',
                'attr' => array('size' => 15,'class'=> 'span2 search-query'))
            )
        ;
    }

    public function getName()
    {
        return 'Mongobox_bundle_jukeboxbundle_search_videos_type';
    }
}
