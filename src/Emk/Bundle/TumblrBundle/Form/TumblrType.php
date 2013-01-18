<?php

namespace Emk\Bundle\TumblrBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;

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
