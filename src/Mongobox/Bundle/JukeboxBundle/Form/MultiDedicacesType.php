<?php

namespace Mongobox\Bundle\JukeboxBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MultiDedicacesType extends AbstractType
{
    public function __construct($groups = array())
    {
        $this->groups = $groups;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['data']['buffPermanente'] == "add") {
            $builder->add('permanente', new DedicacesType($this->groups, $options['data']['permanente']), array('label' => " ", "required" => false));
        }

        $builder->add('playList', new DedicacesType($this->groups, $options['data']['playList']), array('label' => " ", "required" => false));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array());
    }

    public function getName()
    {
        return 'mongobox_bundle_jukeboxbundle_multidedicacestype';
    }
}
