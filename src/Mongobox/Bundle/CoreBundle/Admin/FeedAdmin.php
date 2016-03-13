<?php

namespace Mongobox\Bundle\CoreBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class FeedAdmin extends Admin
{
    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by'    => 'id_feed'
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
            ->add('title', 'text')
            ->add('url', 'text')
            ->add(
                'description',
                'checkbox',
                array(
                    'label'    => 'Description',
                    'required' => false
                )
            )->add(
                'Link',
                'checkbox',
                array(
                    'label'    => 'Link',
                    'required' => false
                )
            )
            ->add(
                'maxItems',
                'text',
                array(
                    'label'    => 'Limit',
                    'required' => false
                )
            )->add(
                'weight',
                'text',
                array(
                    'label'    => 'Weight',
                    'required' => false
                )
            )
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title')
            ->add('url');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('url')
            ->add('description')
            ->add('link')
            ->add('maxItems')
            ->add('weight');
    }


}
