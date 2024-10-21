<?php

declare(strict_types=1);

namespace Guave\DeeplBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class GuaveDeeplBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->booleanNode('enabled')->defaultValue(false)->end()
                ->booleanNode('freeApi')->defaultValue(false)->end()
                ->scalarNode('defaultLanguage')->defaultValue('de')->end()
                ->arrayNode('tables')->useAttributeAsKey('table')
                    ->arrayPrototype()
                    ->children()
                        ->arrayNode('fields')->useAttributeAsKey('field')
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('multiColumnFields')->useAttributeAsKey('multiColumnField')
                            ->arrayPrototype()
                            ->children()
                                ->arrayNode('fields')->useAttributeAsKey('field')
                                    ->scalarPrototype()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
