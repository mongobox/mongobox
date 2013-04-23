<?php

namespace Mongobox\Bundle\JukeboxBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class VideoTagsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tag', 'text', array(
                'label' => 'Tag à ajouter',
                'attr' => array('size' => 15),
                'mapped' => false,
                'required' => false,
            ))
            ->add('tags', 'hidden', array(
                'mapped' => false
            ));
    }

    public function getName()
    {
        return 'video_tags';
    }
}
