<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\DependencyInjection;

use Guave\DeeplBundle\Api\DeeplApi;
use Guave\DeeplBundle\Controller\Backend\DeeplButtons;
use Guave\DeeplBundle\EventListener\LoadDataContainerListener;
use Guave\DeeplBundle\EventListener\LoadFallbackTranslationsListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class GuaveDeeplExtension extends ConfigurableExtension
{
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yml');

        $definition = $container->getDefinition(LoadDataContainerListener::class);

        $definition->setArgument(0, $mergedConfig['enabled']);
        $definition->setArgument(1, $mergedConfig['tables']);

        $definition2 = $container->getDefinition(DeeplButtons::class);
        $definition2->setArgument(0, $mergedConfig['defaultLanguage']);
        $definition2->setArgument(1, $mergedConfig['tables']);

        $definition3 = $container->getDefinition(LoadFallbackTranslationsListener::class);
        $definition3->setArgument(0, $mergedConfig['defaultLanguage']);

        $definition4 = $container->getDefinition(DeeplApi::class);
        $definition4->setArgument(1, $mergedConfig['freeApi']);
    }
}
