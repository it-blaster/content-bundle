<?php

namespace Etfostra\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Propel1\Form\Type\TranslationCollectionType;
use Symfony\Bridge\Propel1\Form\Type\TranslationType;

/**
 * Class PageAdmin
 * @package Etfostra\ContentBundle\Admin
 */
class PageAdmin extends Admin
{
    protected $translationDomain = 'EtfostraContentBundle';

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $container = $this->getConfigurationPool()->getContainer();
        if ($container->hasParameter('locales')) {
            $locales = $container->getParameter('locales');
        } else {
            $locales = array($container->getParameter('locale'));
        }

        if ($container->has('form.type.ckfinder')) {
            $textAreaType = 'ckfinder';
        } elseif ($container->has('ivory_ck_editor.form.type')) {
            $textAreaType = 'ckeditor';
        } else {
            $textAreaType = 'textarea';
        }

        $formMapper
            ->with('Edit', array('class' => 'col-lg-12'))
                ->add('Module', 'choice', array(
                    'required' => false,
                    'choices' => $this->getModuleRouteGroups()
                ))
                ->add('RouteName', null, array(
                    'attr' => array(
                        'readonly' => 'readonly'
                    )
                ))
                ->add('Slug', null, array(
                    'help'  => 'etfostra_slug_hint'
                ))
                ->add('ShowMenu')
                ->add('PageI18ns', new TranslationCollectionType(), array(
                    'label'     => false,
                    'required'  => false,
                    'type'      => new TranslationType(),
                    'languages' => $locales,
                    'options'   => array(
                        'label'      => false,
                        'data_class' => 'Etfostra\ContentBundle\Model\PageI18n',
                        'columns'    => array(
                            'Active' => array(
                                'type' => 'checkbox',
                            ),
                            'Redirect' => array(
                                'type' => 'text',
                                'help'  => 'etfostra_redirect_hint',
                            ),
                            'Title' => array(
                                'type'  => 'text',
                                'required'  => true,
                            ),
                            'Content' => array(
                                'type'  => $textAreaType,
                            ),
                        )
                    )
                ))
            ->end();
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->add('jsonTreeSource')
            ->add('ajaxAdd')
            ->add('ajaxRename')
            ->add('ajaxDelete')
            ->add('ajaxMove')
            ->add('ajaxDetails');
    }

    /**
     * Get array with file name and module name
     *
     * @return array
     */
    private function getModuleRouteGroups()
    {
        $container = $this->getConfigurationPool()->getContainer();
        $route_groups_config = $container->getParameter('etfostra_content.module_route_groups');
        $route_groups = array();
        foreach ($route_groups_config as $route_group) {
            $route_groups[$route_group['routes']] = $route_group['name'];
        }

        return $route_groups;
    }

    /**
     * @param mixed $object
     */
    public function postPersist($object)
    {
        $this->clearRouteCache();
    }

    /**
     * @param mixed $object
     */
    public function postUpdate($object)
    {
        $this->clearRouteCache();
    }

    /**
     * @param mixed $object
     */
    public function postRemove($object)
    {
        $this->clearRouteCache();
    }

    /**
     * Delete all *Url* files in cache directory
     */
    private function clearRouteCache()
    {
        $app_dir = $this->getConfigurationPool()->getContainer()->get('kernel')->getRootDir();
        if ($app_dir) {
            exec('find '.$app_dir.' -type f -name "*Url*" -delete');
        }
    }
}
