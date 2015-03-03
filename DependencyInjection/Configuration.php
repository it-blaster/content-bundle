<?php

namespace Etfostra\ContentBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('etfostra_content');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode
            ->children()
                ->scalarNode('frontend_controllers_namespace')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('page_controller_name')
                    ->cannotBeEmpty()
                    ->defaultValue('EtfostraContentBundle:PageFront:page')
                ->end()
                ->scalarNode('page_template_name')
                    ->cannotBeEmpty()
                    ->defaultValue('EtfostraContentBundle:Front:default.html.twig')
                ->end()
                ->arrayNode('module_route_groups')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->cannotBeEmpty()->end()
                            ->scalarNode('routes')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
