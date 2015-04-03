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

        $root = PageQuery::create()->findRoot();

        if (!$root) {
            $root = new Page();
            $root->setTitle('Home');
            $root->makeRoot();
            $root->save();
        }

        $module_route_groups = $this->getModuleRouteGroups();
        $module_route_array = array();
        foreach ($module_route_groups as $v) {
            $module_route_array[$v['routes']] = $v['name'];
        }

        /** @var Page $page */
        foreach ($root->getBranch() as $page) {
            $current_page = $page;
            $path = array();
            while ($parent_page = $page->getParent()) {
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
            if ($current_page->getModule() && isset($module_route_array[$current_page->getModule()])) {
                $this->addModuleRoutes($current_page, $path_str, $routes);
            }
            // Redirect
            elseif ($current_page->getRedirect()) {
                $this->addRedirectRoute($current_page, $path_str, $routes);
            }
            // Text page
            else {
                $this->addPageRoute($current_page, $path_str, $routes);
            }
        }

        $this->loaded = true;

        return $routes;
    }

    /**
     * @param Page $page
     * @param $path_str
     * @param RouteCollection $routes
     */
    private function addModuleRoutes(Page $page, $path_str, RouteCollection $routes)
    {
        try {
            /** @var RouteCollection $route_collection */
            $route_collection = $this->import(
                $this->getKernel()->locateResource($page->getModule())
            );

            $route_name = '';

            // Find possible index page for module
            /** @var Route $imported_route */
            foreach ($route_collection as $imported_route_name => $imported_route) {
                $trimmed = trim($imported_route->getPath());
                if ($trimmed == '/' || $trimmed == '') {
                    $route_name = $imported_route_name;
                }
            }
            $page->setRouteName($route_name)->save();

            $route_collection->addPrefix($path_str);

            /** @var Route $imported_route */
            foreach ($route_collection as $imported_route_name => $imported_route) {
                $trimmed = rtrim($imported_route->getPath(), '/');
                $imported_route
                    ->setPath($trimmed.'/{page_id}')
                    ->addDefaults(array(
                        'page_id' => (string) $page->getId()
                    ))
                    ->addRequirements(array(
                        'page_id' => (string) $page->getId()
                    ))
                ;
            }

            $routes->addCollection($route_collection);
        } catch (\Exception $e) {
            // do nothing
        }
    }

    /**
     * @param Page $page
     * @param $path_str
     * @param RouteCollection $routes
     * @throws \Exception
     * @throws \PropelException
     */
    private function addRedirectRoute(Page $page, $path_str, RouteCollection $routes)
    {
        $route = new Route(
            $path_str,
            array(
                '_controller'   => 'FrameworkBundle:Redirect:urlRedirect',
                'path'          => (string) $page->getRedirect(),
                'permanent'     => true,
            )
        );

        $route_name = 'etfostra_content_'.$page->getId();

        $routes->add(
            $route_name,
            $route
        );

        $page->setRouteName($route_name)->save();
    }

    /**
     * @param Page $page
     * @param $path_str
     * @param RouteCollection $routes
     * @throws \Exception
     * @throws \PropelException
     */
    private function addPageRoute(Page $page, $path_str, RouteCollection $routes)
    {
        $route = new Route(
            $path_str.'/{page_id}',
            array(
                '_controller'   => $this->getPageControllerName(),
                'page_id'       => (string) $page->getId(),
            ),
            array(
                'page_id' => (string) $page->getId(),
            )
        );

        $route_name = 'etfostra_content_'.$page->getId();

        $routes->add(
            $route_name,
            $route
        );

        $page->setRouteName($route_name)->save();
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