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
        '_sort_by' => 'id'
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('title', 'text')
                ->add('lien', 'text')
                /*->add('url', 'text',array(
                    'required' => false,
                    'data_class' => '\Aml\Bundle\UrlRewriteBundle\Entity\UrlArticle',
                    'read_only' => true
                ))*/
            ->end()
            ->with('Tags')
                ->add('tags', 'sonata_type_model', array(
                    'required' => false,
                    'expanded' => false,
                    'multiple' => true,
                    'by_reference' => false,
                    'attr'=>array('data-sonata-select2'=>'true')
                ))
            ->end()
            ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title')
            ->add('lien')
            ->add('tags')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('thumbnail', 'string', array('template' => 'MongoboxJukeboxBundle:Admin:list_thumbnail.html.twig'))
            ->addIdentifier('title')
            ->add('lien')
            ->add('date')
            ->add('tags')
        ;
    }


}