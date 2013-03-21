<?php

namespace Mongobox\Bundle\JukeboxBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DedicacesType extends AbstractType
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
            ->add('text')
            ->add('groups', 'choice', array(
                'label' => 'Publier dans ces groupes',
                'choices' => $this->groups,
                'multiple' => true,
                'expanded' => true,
                'property_path' => false
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mongobox\Bundle\JukeboxBundle\Entity\Dedicaces'
        ));
    }

    public function getName()
    {
        return 'mongobox_bundle_jukeboxbundle_dedicacestype';
    }
}
