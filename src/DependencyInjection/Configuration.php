<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('guave_deepl');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('enabled')->defaultValue(false)->end()
                ->booleanNode('freeApi')->defaultValue(false)->end()
                ->scalarNode('defaultLanguage')->defaultValue('de')->end()
                ->arrayNode('tables')
                    ->useAttributeAsKey('table')->arrayPrototype()
                        ->children()
                            ->arrayNode('fields')->useAttributeAsKey('field')->scalarPrototype()->end()->end()
                            ->arrayNode('multiColumnFields')
                                ->useAttributeAsKey('multiColumnField')->arrayPrototype()
                                    ->children()
                                        ->arrayNode('fields')->useAttributeAsKey('field')->scalarPrototype()->end()->end()
                                    ->end()
                            ->end()
                        ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
