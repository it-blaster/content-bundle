<?php

namespace Etfostra\ContentBundle\Controller;

use Etfostra\ContentBundle\Model\Page;
use Etfostra\ContentBundle\Model\PageQuery;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class PageAdminController
 * @package Etfostra\ContentBundle\Controller
 */
class PageAdminController extends CRUDController
{
    /**
     * Tree view page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        return $this->render('EtfostraContentBundle:SonataAdmin:list.html.twig', array(
            'action' => 'list'
        ));
    }

    /**
     * JSON tree structure for jsTree
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function jsonTreeSourceAction(Request $request)
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $root = PageQuery::create()->findRoot();

        if (!$root) {
            $root = new Page();
            $root->setLocale($request->getLocale());
            $root->setTitle('Home');
            $root->makeRoot();
            $root->save();
        }

        $root->setLocale($request->getLocale());

        $buildTree = function(Page $root, &$buildTree) {
            $array_nodes = array();

            /** @var Page $node */
            foreach ($root->getChildren() as $node) {
                $node->setLocale($root->getLocale());
                $array_node = array();
                $array_node['id'] = $node->getId();
                $array_node['text'] = $node->getTitle();
                if ($node->getModule()) {
                    $array_node['type'] = 'module';
                } else {
                    $array_node['type'] = 'default';
                }
                if ($node->hasChildren()) {
                    $array_node['children'] = $buildTree($node, $buildTree);
                }

                $array_nodes[] = $array_node;
            }

            return $array_nodes;
        };

        $data_tree = array(
            'id' => $root->getId(),
            'text' => $root->getTitle(),
            'type' => 'root',
            'children' => $buildTree($root, $buildTree)
        );

        return new JsonResponse($data_tree);
    }

    /**
     * Ajax add Page
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     * @throws \PropelException
     */
    public function ajaxAddAction(Request $request)
    {
        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $parent = PageQuery::create()->findOneById($request->query->get('parentId'));

        if (!$parent) {
            throw new \Exception();
        }

        $page = new Page();
        $page->setLocale($request->getLocale());
        $page->setTitle($request->query->get('name'));
        $page->insertAsLastChildOf($parent);
        $page->save();

        $this->clearRouteCache();

        return new JsonResponse(array(
            'id' => $page->getId()
        ));
    }

    /**
     * Ajax rename Page
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     * @throws \PropelException
     */
    public function ajaxRenameAction(Request $request)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $page = PageQuery::create()->findOneById($request->query->get('id'));

        if (!$page) {
            throw new \Exception();
        }

        $initial_slug = $page->getSlug();

        $page
            ->setLocale($request->getLocale())
            ->setTitle($request->query->get('name'));

        $page->save();

        if ($page->getSlug() != $initial_slug) {
            $this->clearRouteCache();
        }

        return new JsonResponse(array());
    }

    /**
     * Ajax delete Page
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     * @throws \PropelException
     */
    public function ajaxDeleteAction(Request $request)
    {
        if (false === $this->admin->isGranted('DELETE')) {
            throw new AccessDeniedException();
        }

        $page = PageQuery::create()->findOneById($request->query->get('id'));
        $page->setLocale($request->getLocale());

        $page->delete();

        $this->clearRouteCache();

        return new JsonResponse(array());
    }

    /**
     * Ajax move (drag'n'drop) Page
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     * @throws \PropelException
     */
    public function ajaxMoveAction(Request $request)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $page_id = (int) $request->query->get('id');
        $parent_id = (int) $request->query->get('parent');
        $position = abs((int) $request->query->get('position'));

        $page = PageQuery::create()->findOneById($page_id);
        $page->setLocale($request->getLocale());

        $parent_page = PageQuery::create()->findOneById($parent_id);
        $parent_page->setLocale($request->getLocale());
        if ($position == 0) {
            $page->moveToFirstChildOf($parent_page);
        } else {
            $page->moveToLastChildOf($parent_page);
            $siblings = $parent_page->getChildren();
            $current_position = 0;
            /** @var Page $sibling */
            foreach ($siblings as $sibling) {
                $sibling->setLocale($parent_page->getLocale());
                if ($current_position++ == $position-1) {
                    $page->moveToNextSiblingOf($sibling);
                    break;
                }
            }
        }

        $page->save();

        $this->clearRouteCache();

        return new JsonResponse(array());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     * @throws \PropelException
     */
    public function ajaxDetailsAction(Request $request)
    {
        if (false === $this->admin->isGranted('VIEW')) {
            throw new AccessDeniedException();
        }

        $page = PageQuery::create()->findOneById($request->query->get('id'));
        if (!$page) {
            throw new NotFoundHttpException();
        }
        $page->setLocale($request->getLocale());
        if($page->hasParent()) {
            $page->getParent()->setLocale($page->getLocale());
        }

        $html = $this->renderView('EtfostraContentBundle:SonataAdmin:list_details.html.twig', array(
            'page' => $page
        ));

        return new JsonResponse(array(
            'html' => $html
        ));
    }

    /**
     * Delete all *Url* files in cache directory
     */
    private function clearRouteCache()
    {
        $app_dir = $this->get('kernel')->getRootDir();
        if ($app_dir) {
            exec('find '.$app_dir.' -type f -name "*Url*" -delete');
        }
    }
}
