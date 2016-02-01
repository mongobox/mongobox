<?php

namespace Mongobox\Bundle\GroupBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class GroupAdmin extends Admin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
            ->add(
                'name',
                'text',
                array(
                    'label' => 'Titre',
                    'attr'  => array('size' => 15, 'placeholder' => 'Titre du groupe')
                )
            )
            ->add(
                'city',
                'text',
                array(
                    'label' => 'Ville',
                    'attr'  => array('placeholder' => 'Ville où se situe le groupe')
                )
            )
            ->add(
                'private',
                'checkbox',
                array(
                    'label' => 'Privé',
                    'required'     => false,
                )
            )
            ->add(
                'liveMaxDislikes',
                'integer',
                array(
                    'label' => 'Nombre maximum de votes négatifs sur une vidéo',
                    'attr'  => array('min' => 1, 'placeholder' => 'Renseigner un nombre maximum')
                )
            )
            ->add(
                'nextPutschWaiting',
                'integer',
                array(
                    'label' => 'Temps entre chaque tentative de putsch sur le live (minutes)',
                    'attr'  => array('min' => 1, 'placeholder' => 'Renseigner un temps')
                )
            )
            ->end()
            ->with('Membres')
            ->add(
                'users',
                'sonata_type_model',
                array(
                    'required'     => false,
                    'expanded'     => true,
                    'multiple'     => true,
                    'by_reference' => false
                )
            )
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('private');
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($group)
    {
        $container = $this->getConfigurationPool()->getContainer();
        $secreyKey = $container->get('mongobox_jukebox.live_configurator')->generateSecretKey();

        $group
            ->setSecretKey($secreyKey);
    }
}
