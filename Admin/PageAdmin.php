<?php

namespace Etfostra\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class PageAdmin extends Admin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('Id')
            ->add('Module')
            ->add('CreatedAt')
            ->add('UpdatedAt')
            ->add('Slug')
            ->add('TreeLeft')
            ->add('TreeRight')
            ->add('TreeLevel')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('Id')
            ->add('Module')
            ->add('CreatedAt')
            ->add('UpdatedAt')
            ->add('Slug')
            ->add('TreeLeft')
            ->add('TreeRight')
            ->add('TreeLevel')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('Id')
            ->add('Module')
            ->add('CreatedAt')
            ->add('UpdatedAt')
            ->add('Slug')
            ->add('TreeLeft')
            ->add('TreeRight')
            ->add('TreeLevel')
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('Id')
            ->add('Module')
            ->add('CreatedAt')
            ->add('UpdatedAt')
            ->add('Slug')
            ->add('TreeLeft')
            ->add('TreeRight')
            ->add('TreeLevel')
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->add('jsonTreeSource')
            ->add('ajaxAdd')
            ->add('ajaxRename')
            ->add('ajaxDelete')
            ->add('ajaxMove');
    }
}
