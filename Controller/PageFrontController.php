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
     * @param integer $page_id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pageAction(Request $request, $page_id)
    {
        $page = $this->getPageById($page_id);

        if ($page->getRedirect()) {
            return $this->forward('EtfostraContentBundle:PageFront:redirect', array(
                'request'   => $request,
                'page'      => $page,
            ));
        }

        $template = $this->container->getParameter('etfostra_content.page_template_name');

        return $this->render($template, array(
            'page'          => $page,
            'breadcrumbs'   => $this->getBreadcrumbs($page, $this->get('router')),
            'mainmenu'      => $this->getMainMenu($this->get('router'), true),
            'menu'          => $this->getMenu($page, $this->get('router'), true),
            'submenu'       => $this->getSubMenu($page, $this->get('router')),
        ));
    }

    /**
     * Redirect action for redirect type Page
     *
     * @param Request $request
     * @param Page $page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function redirectAction(Request $request, Page $page)
    {
        $path = $page->getRedirect();

        return $this->forward('FrameworkBundle:Redirect:urlRedirect', array(
            'request'   => $request,
            'path'      => $path,
            'permanent' => true,
        ));
    }

    /**
     * Return array for breadcrumbs generation
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
            $item = array(
                'page' => $ancestor,
                'title' => $ancestor->getTitle(),
                'link' => $this->generatePageLink($ancestor, $router),
                'active' => $this->isPageActive($ancestor),
            );

            // Siblings
            if ($with_siblings) {
                $siblings = $ancestor->getSiblings(true);
                /** @var Page $sibling */
                foreach ($siblings as $sibling) {
                    $sibling->setLocale($page->getLocale());
                    if (!isset($item['siblings'])) {
                        $item['siblings'] = array();
                    }
                    $subitem = array(
                        'page'      => $sibling,
                        'title'     => $sibling->getTitle(),
                        'link'      => $this->generatePageLink($sibling, $router),
                        'active'    => $this->isPageActive($sibling),
                    );

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
        $siblings = $page->getSiblings(true, $this->getShowMenuCriteria());
        $menu = array();

        /** @var Page $sibling */
        foreach ($siblings as $sibling) {
            $sibling->setLocale($page->getLocale());
            $item = array(
                'page'      => $sibling,
                'title'     => $sibling->getTitle(),
                'link'      => $this->generatePageLink($sibling, $router),
                'active'    => $this->isPageActive($sibling),
                'subactive' => $this->isPageSubActive($sibling),
            );

            if ($with_children) {
                $item['children'] = $this->getSubMenu($sibling, $router);
            }

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
        $childrens = $page->getChildren($this->getShowMenuCriteria());
        $menu = array();

        /** @var Page $children */
        foreach ($childrens as $children) {
            $children->setLocale($page->getLocale());
            $item = array(
                'page'      => $children,
                'title'     => $children->getTitle(),
                'link'      => $this->generatePageLink($children, $router),
                'active'    => $this->isPageActive($children),
                'subactive' => $this->isPageSubActive($children),
            );

            $menu[] = $item;
        }

        return $menu;
    }

    /**
     * Return array of root children for main menu generation
     *
     * @param Router $router
     * @param bool $with_children
     * @return array
     */
    public function getMainMenu(Router $router, $with_children = false)
    {
        $page = PageQuery::create()->findRoot();

        $page->setLocale($this->get('request')->getLocale());

        $menu = $this->getSubMenu($page, $router);

        if ($with_children) {
            foreach ($menu as $k=>$item) {
                if ($with_children) {
                    $menu[$k]['children'] = $this->getSubMenu($item['page'], $router);
                }
            }
        }

        return $menu;
    }

    /**
     * Generates link for page
     *
     * @param Page $page
     * @param Router $router
     * @return null|string
     */
    protected function generatePageLink(Page $page, Router $router)
    {
        $route_name = $page->getRouteName();

        if ($route_name) {
            return $router->generate($route_name);
        } else {
            return null;
        }
    }

    /**
     * Is current route match page
     *
     * @param Page $page
     * @return bool
     */
    protected function isPageActive(Page $page)
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
     * Is current route match any child of page
     *
     * @param Page $page
     * @return bool
     */
    protected function isPageSubActive(Page $page)
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

    /**
     * @return \Criteria
     */
    protected function getShowMenuCriteria()
    {
        $q = PageQuery::create()
            ->filterByShowMenu(true)
            ->useI18nQuery($this->get('request')->getLocale())
                ->filterByActive(true)
            ->endUse();

        return $q;
    }

    /**
     * Get page by id, if page not found or inactive thow exception
     *
     * @param $page_id
     * @return Page
     */
    protected function getPageById($page_id)
    {
        $page = PageQuery::create()->findOneById($page_id);

        if (!$page) {
            throw $this->createNotFoundException($this->get('translator')->trans('etfostra_front_page_not_found'));
        }

        $page->setLocale($this->get('request')->getLocale());

        if (!$page->getActive()) {
            throw $this->createNotFoundException($this->get('translator')->trans('etfostra_front_page_not_found'));
        }

        return $page;
    }
}