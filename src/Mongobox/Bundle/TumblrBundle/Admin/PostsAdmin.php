<?php

namespace Mongobox\Bundle\TumblrBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PostsAdmin extends Admin
{
    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'id_tumblr'
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        // get the current Post instance
        $tumblrPost = $this->getSubject();

        // use $fileFieldOptions so we can add other options to the field
        $imageFieldOptions = array('required' => false);
        if ($tumblrPost) {
            $image = $tumblrPost->getImage();
            $localImage = $tumblrPost->getLocalPath();

            if($localImage){
                $image = $localImage;
            }
            // add a 'help' option containing the preview's img tag
            $imageFieldOptions['help'] = '<img src="'.$image.'" class="admin-preview" />';
        }

        $formMapper
            ->with('General')
                ->add('text', 'text')
                ->add('image', 'text',$imageFieldOptions)
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
            ->add('text')
            ->add('tags')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('text')
            ->add('date')
            ->add('tags')
        ;
    }


}