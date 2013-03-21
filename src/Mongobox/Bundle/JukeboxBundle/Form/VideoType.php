<?php

namespace Mongobox\Bundle\JukeboxBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class VideoType extends AbstractType
{
    public function __construct($groups = array())
    {
        $this->groups = $groups;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lien', 'text', array(
                'label' => 'Lien Youtube ou Id de la vidéo',
                'attr' => array('size' => 15)
            ))
            ->add('dedicaces', new DedicacesType($this->groups))
        ;
    }

    public function getName()
    {
        return 'video';
    }
}
