<?php

namespace Mongobox\Bundle\JukeboxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VideoTagsType
 * @package Mongobox\Bundle\JukeboxBundle\Form
 */
class VideoTagsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'tag',
                'text',
                array(
                    'label'    => 'Tag à ajouter',
                    'attr'     => array('size' => 15),
                    'mapped'   => false,
                    'required' => false,
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

    public function getName()
    {
        return 'video_tags';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'intention' => $this->getName(),
        ));
    }
}
