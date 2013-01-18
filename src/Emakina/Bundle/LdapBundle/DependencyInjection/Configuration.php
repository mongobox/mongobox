<?php

namespace Emakina\Bundle\LdapBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('emakina_ldap');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode
            ->children()
                ->arrayNode('options')
                ->children()
                    ->scalarNode('host')
                        ->isRequired()
                    ->end()
                    ->scalarNode('username')
                        ->isRequired()
                    ->end()
                    ->scalarNode('password')
                        ->isRequired()
                    ->end()
                    ->scalarNode('accountDomainName')
                        ->isRequired()
                    ->end()
					->scalarNode('accountDomainNameShort')
						->isRequired()
                	->end()
					
                    /*->scalarNode('accountCanonicalForm')
                        ->isRequired()
                    ->end()*/
                    ->scalarNode('baseDn')
                        ->isRequired()
                    ->end()
                    ->booleanNode('useStartTls')
                        ->isRequired()
                    ->end()
                ->end()
            ->end();


        return $treeBuilder;
    }
}
