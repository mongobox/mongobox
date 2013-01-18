<?php

namespace Emk\Bundle\JukeboxBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VideosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lien')
            ->add('date')
            ->add('vendredi')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Emk\Bundle\JukeboxBundle\Entity\Videos'
        ));
    }

    public function getName()
    {
        return 'emk_bundle_jukeboxbundle_videostype';
    }
}
