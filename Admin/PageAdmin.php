<?php

namespace Etfostra\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Propel1\Form\Type\TranslationCollectionType;
use Symfony\Bridge\Propel1\Form\Type\TranslationType;

class PageAdmin extends Admin
{

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Переводы', ['class'=>'col-lg-12'])
                ->add('Module')
                ->add('Slug', null, [
                    'help'  => 'etfostra_slug_hint'
                ])
                ->add('PageI18ns', new TranslationCollectionType(), [
                    'label'     => false,
                    'required'  => false,
                    'type'      => new TranslationType(),
                    'languages' => $this->getConfigurationPool()->getContainer()->getParameter('locales'),
                    'options'   => [
                        'label'      => false,
                        'data_class' => 'Etfostra\ContentBundle\Model\PageI18n',
                        'columns'    => [
                            'Active' => [
                                'type' => 'checkbox',
                            ],
                            'Title' => [
                                'type'  => 'text',
                                'required'  => true,
                            ],
                            'Content' => [
                                'type'  => 'ckeditor',
                            ],
                        ]
                    ]
                ])
            ->end();
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
