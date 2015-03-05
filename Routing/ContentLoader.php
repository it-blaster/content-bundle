<?php

namespace Etfostra\ContentBundle\Routing;

use Etfostra\ContentBundle\Model\Page;
use Etfostra\ContentBundle\Model\PageQuery;
use Propel\Common\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Dynamic routing
 *
 * Class ContentLoader
 * @package Etfostra\ContentBundle\Routing
 */
class ContentLoader extends Loader
{
    protected $page_controller_name;
    protected $module_route_groups;
    protected $kernel;

    private $loaded = false;

    /**
     * @param $page_controller_name
     * @param $module_route_groups
     * @param $kernel
     */
    public function __construct($page_controller_name, $module_route_groups, $kernel)
    {
        $this->setPageControllerName($page_controller_name);
        $this->setModuleRouteGroups($module_route_groups);
        $this->setKernel($kernel);
        $this->setResolver(new LoaderResolver());
    }

    /**
     * @return mixed
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * @param mixed $kernel
     */
    public function setKernel($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return string
     */
    public function getPageControllerName()
    {
        return $this->page_controller_name;
    }

    /**
     * @param string $page_controller_name
     */
    public function setPageControllerName($page_controller_name)
    {
        $this->page_controller_name = $page_controller_name;
    }

    /**
     * @return mixed
     */
    public function getModuleRouteGroups()
    {
        return $this->module_route_groups;
    }

    /**
     * @param mixed $module_route_groups
     */
    public function setModuleRouteGroups($module_route_groups)
    {
        $this->module_route_groups = $module_route_groups;
    }

    /**
     * @param mixed $resource
     * @param null $type
     * @return RouteCollection
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $routes = new RouteCollection();

        $pages = PageQuery::create()->findRoot();

        $page_controller_name = $this->getPageControllerName();

        $module_route_groups = $this->getModuleRouteGroups();
        $module_route_array = array();
        foreach($module_route_groups as $v) {
            $module_route_array[$v['routes']] = $v['name'];
        }

        /** @var Page $page */
        foreach ($pages->getBranch() as $page) {
            $current_page = $page;
            $path = array();
            while($parent_page = $page->getParent()) {
                $path[] = $page;
                $page = $parent_page;
            }

            $path = array_reverse($path);

            $path_str = '';
            /** @var Page $path_part */
            foreach ($path as $path_part) {
                $path_str .= '/'.$path_part->getSlug();
            }

            // Module (routes group)
            if($current_page->getModule() && isset($module_route_array[$current_page->getModule()])) {
                try {
                    /** @var RouteCollection $route_collection */
                    $route_collection = $this->import(
                        $this->getKernel()->locateResource($current_page->getModule())
                    );

                    $route_collection->addPrefix($path_str);

                    /** @var Route $imported_route */
                    foreach($route_collection as $imported_route) {
                        $trimmed = rtrim($imported_route->getPath(), '/');
                        $imported_route->setPath($trimmed);
                    }

                    $routes->addCollection($route_collection);
                } catch (\Exception $e) {
                    // do nothing
                }
            }
            // Text page
            else {
                $route = new Route(
                    $path_str.'/{id}',
                    array(
                        '_controller' => $page_controller_name,
                        'id' => (string) $current_page->getId()
                    ),
                    array(
                        'id' => (string) $current_page->getId()
                    )
                );

                $routes->add(
                    'etfostra_content_'.str_replace(array('-', '/'),'_',$path_str),
                    $route
                );
            }
        }

        $this->loaded = true;

        return $routes;
    }

    /**
     * @param mixed $resource
     * @param null $type
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        return 'extra' === $type;
    }

    /**
     * @return mixed
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     * @param LoaderResolverInterface $resolver
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }
}