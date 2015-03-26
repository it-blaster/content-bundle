<?php
namespace Etfostra\ContentBundle\Twig;

use Symfony\Component\Routing\Router;

/**
 * Class EtfostraContentExtension
 * @package Etfostra\ContentBundle\Twig
 */
class EtfostraContentExtension extends \Twig_Extension
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('page_path', array($this, 'pageLinkFunction')),
        );
    }

    /**
     * @param $route_name
     * @return string
     */
    public function pageLinkFunction($route_name)
    {
        try {
            $link = $this->router->generate($route_name);
        } catch (\Exception $e) {
            $link = '';
        }

        return $link;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'page_path';
    }
}