<?php

namespace Mongobox\Bundle\JukeboxBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class VideoAdmin extends Admin
{
    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by'    => 'id'
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        // get the current Post instance
        $video = $this->getSubject();

        // use $thumbnailFieldOptions so we can add other options to the field
        $thumbnailFieldOptions = array('required' => false, 'read_only' => true);
        if ($video && $thumbnail = $video->getThumbnail()) {
            // add a 'help' option containing the preview's img tag
            $thumbnailFieldOptions['help'] = '<img src="' . $thumbnail . '" class="admin-preview" />';
        }

        // use $thumbnailFieldOptions so we can add other options to the field
        $lienFieldOptions = array('required' => true);
        if ($video && $lien = $video->getLien()) {
            // add a 'help' option containing the preview's img tag
            $urlVideo = "https://www.youtube.com/embed/{$lien}";
            $lienFieldOptions['help'] = '<iframe width="300" height="169"
src="' . $urlVideo . '" frameborder="0" allowfullscreen></iframe>';
        }

        $formMapper
            ->with('General')
            ->add('title', 'text')
            ->add('lien', 'text', $lienFieldOptions)
            ->add('duration', 'text')
            ->add('thumbnail', 'text', $thumbnailFieldOptions)
            ->add(
                'artist',
                'text',
                array(
                    'label' => 'Artiste'
                )
            )
            ->add(
                'songName',
                'text',
                array(
                    'label' => 'Nom de la vidéo'
                )
            )
            ->end()
            ->with('Tags')
            ->add(
                'tags',
                'sonata_type_model',
                array(
                    'required'     => false,
                    'expanded'     => false,
                    'multiple'     => true,
                    'by_reference' => false,
                    'attr'         => array('data-sonata-select2' => 'true')
                )
            )
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title')
            ->add('lien')
            ->add('tags');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('thumbnail', 'string', array('template' => 'MongoboxJukeboxBundle:Admin:list_thumbnail.html.twig'))
            ->addIdentifier('title')
            ->add('lien')
            ->add('date')
            ->add('tags');
    }
}
