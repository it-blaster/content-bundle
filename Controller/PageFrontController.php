<?php

namespace Etfostra\ContentBundle\Controller;

use Etfostra\ContentBundle\Model\PageQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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

        if(!$page) {
            throw $this->createNotFoundException($this->get('translator')->trans('etfostra_front_page_not_found'));
        }

        $page->setLocale($request->getLocale());

        if(!$page->getActive()) {
            throw $this->createNotFoundException($this->get('translator')->trans('etfostra_front_page_not_found'));
        }

        $template = $this->container->getParameter('etfostra_content.page_template_name');

        return $this->render($template, array(
            'page' => $page
        ));
    }
}