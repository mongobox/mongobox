<?php

namespace Mongobox\Bundle\JukeboxBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TumblrType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image', 'text', array(
                'label' => 'Lien vers l\'image'
            ))
            ->add('text', 'text', array(
                'label' => 'Texte'
            ))
        ;
    }

    public function getName()
    {
        return 'tumblr';
    }
}
