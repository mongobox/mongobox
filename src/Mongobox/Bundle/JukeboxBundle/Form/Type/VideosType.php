<?php

namespace Mongobox\Bundle\JukeboxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VideosType
 * @package Mongobox\Bundle\JukeboxBundle\Form
 */
class VideosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lien');
    }

    public function getName()
    {
        return 'Mongobox_bundle_jukeboxbundle_videostype';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mongobox\Bundle\JukeboxBundle\Entity\Videos',
            'intention' => $this->getName(),
        ));
    }
}
