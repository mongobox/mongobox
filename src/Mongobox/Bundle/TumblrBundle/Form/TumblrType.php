<?php

namespace Mongobox\Bundle\TumblrBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;

class TumblrType extends AbstractType
{
	public function __construct($groups = array())
	{
		$this->groups = array();
		foreach($groups as $group)
		{
			$this->groups[$group->getId()] = $group->getTitle();
		}
	}

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
			$builder->add('groups', 'choice', array(
            	'label' => 'Partager dans ces groups',
				'choices' => $this->groups,
				'multiple' => true,
				'expanded' => true,
				'property_path' => false
            ));
	}

    public function getName()
    {
        return 'tumblr';
    }
}
