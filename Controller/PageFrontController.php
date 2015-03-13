<?php

namespace Etfostra\ContentBundle\Controller;

use Etfostra\ContentBundle\Model\Page;
use Etfostra\ContentBundle\Model\PageQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

/**
 * Defaul Page render controller
 *
 * Class PageFrontController
 * @package Etfostra\ContentBundle\Controller
 */
class PageFrontController extends Controller
{
    /**
     * Default Page render action
     *
     * @param Request $request
     * @param integer $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pageAction(Request $request, $id)
    {
        $page = PageQuery::create()->findOneById($id);

        if (!$page) {
            throw $this->createNotFoundException($this->get('translator')->trans('etfostra_front_page_not_found'));
        }

        $page->setLocale($request->getLocale());

        if (!$page->getActive()) {
            throw $this->createNotFoundException($this->get('translator')->trans('etfostra_front_page_not_found'));
        }

        $template = $this->container->getParameter('etfostra_content.page_template_name');

        return $this->render($template, array(
            'page'          => $page,
            'breadcrumbs'   => $this->getBreadcrumbs($page, $this->get('router')),
            'mainmenu'      => $this->getMainMenu($this->get('router')),
            'menu'          => $this->getMenu($page, $this->get('router'), true),
            'submenu'       => $this->getSubMenu($page, $this->get('router')),
        ));
    }

    /**
     * Retrun array for breadcrumbs generation
     * array(
     *     array(title, link, [active], [siblings]),
     * )
     *
     * @param Page $page
     * @param Router $router
     * @param bool $with_siblings
     * @return array
     */
    public function getBreadcrumbs(Page $page, Router $router, $with_siblings = false)
    {
        /** @var \PropelObjectCollection $ancestors */
        $ancestors = $page->getAncestors();

        if (!$ancestors) {
            return array();
        }

        $path = array();

        $ancestors->append($page);

        /** @var Page $ancestor */
        foreach ($ancestors as $ancestor) {
            $ancestor->setLocale($page->getLocale());
            $item = array();
            $item['title'] = $ancestor->getTitle();
            $item['link'] = $this->generatePageLink($ancestor, $router);
            $item['active'] = $this->isPageActive($ancestor);

            // Siblings
            if ($with_siblings) {
                $siblings = $ancestor->getSiblings(true);
                /** @var Page $sibling */
                foreach ($siblings as $sibling) {
                    $sibling->setLocale($page->getLocale());
                    $subitem = array();
                    if (!isset($item['siblings'])) {
                        $item['siblings'] = array();
                    }
                    $subitem['title'] = $sibling->getTitle();
                    $subitem['link'] = $this->generatePageLink($sibling, $router);
                    $subitem['active'] = $this->isPageActive($sibling);

                    $item['siblings'][] = $subitem;
                }
            }

            $path[] = $item;
        }

        return $path;
    }

    /**
     * Return array of siblings for menu generation
     *
     * @param Page $page
     * @param Router $router
     * @param bool $with_children
     * @return array
     */
    public function getMenu(Page $page, Router $router, $with_children = false)
    {
        $siblings = $page->getSiblings(true);
        $menu = array();

        /** @var Page $sibling */
        foreach ($siblings as $sibling) {
            $sibling->setLocale($page->getLocale());
            $item = array();
            $item['title'] = $sibling->getTitle();
            $item['link'] = $this->generatePageLink($sibling, $router);
            $item['active'] = $this->isPageActive($sibling);

            if ($with_children) {
                $item['children'] = $this->getSubMenu($sibling, $router);
            }

            // Is any of child active?
            $item['subactive'] = $this->isPageSubActive($sibling);

            $menu[] = $item;
        }

        return $menu;
    }

    /**
     * Return array of siblings for menu generation
     *
     * @param Page $page
     * @param Router $router
     * @return array
     */
    public function getSubMenu(Page $page, Router $router)
    {
        $childrens = $page->getChildren();
        $current_route = $this->container->get('request')->get('_route');
        $menu = array();

        /** @var Page $children */
        foreach ($childrens as $children) {
            $children->setLocale($page->getLocale());
            $item = array();
            $item['title'] = $children->getTitle();
            $item['link'] = $this->generatePageLink($children, $router);
            $item['active'] = $this->isPageActive($children);

            // Is any of child active?
            $item['subactive'] = $this->isPageSubActive($children);

            $menu[] = $item;
        }

        return $menu;
    }

    /**
     * @param Router $router
     * @param bool $with_children
     * @return array
     */
    public function getMainMenu(Router $router, $with_children = false)
    {
        $page = PageQuery::create()->findRoot();

        return $this->getSubMenu($page, $router, $with_children);
    }

    /**
     * @param Page $page
     * @param Router $router
     * @return null|string
     */
    private function generatePageLink(Page $page, Router $router)
    {
        $route_name = $page->getRouteName();

        if ($route_name) {
            return $router->generate($route_name);
        } else {
            return null;
        }
    }

    /**
     * @param Page $page
     * @return bool
     */
    private function isPageActive(Page $page)
    {
        $page_route_name = $page->getRouteName();
        $current_route_name = $this->get('request')->get('_route');

        if ($page_route_name == $current_route_name) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Page $page
     * @return bool
     */
    private function isPageSubActive(Page $page)
    {
        $current_route_name = $this->get('request')->get('_route');

        if ($page->getRouteName() == $current_route_name) {
            return false;
        }

        $possible_child = PageQuery::create()->findOneByRouteName($current_route_name);

        if (!$possible_child) {
            return false;
        }

        if ($page->isAncestorOf($possible_child)) {
            return true;
        } else {
            return false;
        }
    }
}