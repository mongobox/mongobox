<?php

namespace Mongobox\Bundle\GroupBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
            	'label' => 'Titre',
            	'attr'  => array('size' => 15, 'placeholder' => 'Titre du groupe')
            ))
            ->add('city', 'text', array(
		        'label' => 'Ville',
		        'attr' => array('placeholder' => 'Ville où se situe le groupe')
	        ))
            ->add('private', 'checkbox', array(
            	'label' => 'Privé'
            ))
            ->add('liveMaxDislikes', 'integer', array(
                'label' => 'Nombre maximum de votes négatifs sur une vidéo',
                'attr'  => array('min' => 1, 'placeholder' => 'Renseigner un nombre maximum')
            ))
            ->add('nextPutschWaiting', 'integer', array(
                'label' => 'Temps entre chaque tentative de putsch sur le live (minutes)',
                'attr'  => array('min' => 1, 'placeholder' => 'Renseigner un temps')
            ))
        ;
	}

    public function getName()
    {
        return 'group_create';
    }
}
