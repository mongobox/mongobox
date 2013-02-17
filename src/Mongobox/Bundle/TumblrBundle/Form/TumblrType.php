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
			if($group->getPrivate()) $this->groups[$group->getId()] .= ' <i class="icon-lock" title="Groupe Privé"></i>';
			else $this->groups[$group->getId()] .= ' <i class="icon-globe" title="Groupe Publique"></i>';
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
            ->add('addtags', 'genemu_jqueryautocompleter_entity', array(
                'route_name' => 'tumblr_tags_ajax_autocomplete',
                'class' => 'Mongobox\Bundle\TumblrBundle\Entity\TumblrTag',
                'property' => 'name',
                'label' => 'Tags',
                'attr' => array(
                    'placeholder' => 'Ajouter des tags',
                ),
                'required' => false,
                'property_path' => false
            ))
            ->add('tags', 'hidden', array(
                'data' => array('toto',"titi"),
                'property_path' => false

            ))
            ->add('groups', 'choice', array(
                'label' => 'Partager dans ces groupes',
                'choices' => $this->groups,
                'multiple' => true,
                'expanded' => true,
                'property_path' => false
            ))
        ;

	}

    public function getName()
    {
        return 'tumblr';
    }
}
