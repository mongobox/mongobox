<?php

namespace Mongoeat\Bundle\FoursquareBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('mongoeat_foursquare');

        $rootNode
            ->children()
                ->arrayNode('authentification')
                    ->children()
                        ->scalarNode('id')->isRequired()->end()
                        ->scalarNode('secret')->isRequired()->end()
                        ->scalarNode('url_auth')->isRequired()->end()
                        ->scalarNode('url_api')->isRequired()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
