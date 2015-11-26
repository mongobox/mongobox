<?php

namespace Mongobox\Bundle\JukeboxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TumblrType
 * @package Mongobox\Bundle\JukeboxBundle\Form
 */
class TumblrType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'image',
                'text',
                array(
                    'label' => 'Lien vers l\'image'
                )
            )
            ->add(
                'text',
                'text',
                array(
                    'label' => 'Texte'
                )
            );
    }

    public function getName()
    {
        return 'tumblr';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'intention' => $this->getName(),
        ));
    }
}
