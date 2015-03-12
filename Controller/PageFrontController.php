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
            'menu'          => $this->getMenu($page, $this->get('router')),
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

        if(!$ancestors) return array();
        $path = array();

        $ancestors->append($page);

        /** @var Page $ancestor */
        foreach ($ancestors as $ancestor) {
            $ancestor->setLocale($page->getLocale());
            $item = array();
            $item['title'] = $ancestor->getTitle();
            if ($ancestor->getRouteName()) {
                $item['link'] = $router->generate($ancestor->getRouteName());
            }
            if ($page->getRouteName() == $ancestor->getRouteName()) {
                $item['active'] = true;
            }

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
                    if ($sibling->getRouteName()) {
                        $subitem['link'] = $router->generate($sibling->getRouteName());
                    }
                    if ($page->getRouteName() == $sibling->getRouteName()) {
                        $subitem['active'] = true;
                    }

                    $item['siblings'][] = $subitem;
                }
            }

            $path[] = $item;
        }

        return $path;
    }

    /**
     * @param Page $page
     * @param Router $router
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
            if ($sibling->getRouteName()) {
                $item['link'] = $router->generate($sibling->getRouteName());
            }
            if ($page->getRouteName() == $sibling->getRouteName()) {
                $item['active'] = true;
            }

            if ($with_children) {
                $item['children'] = $this->getSubMenu($sibling, $router);
            }

            $menu[] = $item;
        }

        return $menu;
    }

    /**
     * @param Page $page
     * @param Router $router
     * @return array
     */
    public function getSubMenu(Page $page, Router $router)
    {
        $childrens = $page->getChildren();
        $menu = array();

        /** @var Page $children */
        foreach ($childrens as $children) {
            $children->setLocale($page->getLocale());
            $item = array();
            $item['title'] = $children->getTitle();
            if ($children->getRouteName()) {
                $item['link'] = $router->generate($children->getRouteName());
            }

            $menu[] = $item;
        }

        return $menu;
    }
}