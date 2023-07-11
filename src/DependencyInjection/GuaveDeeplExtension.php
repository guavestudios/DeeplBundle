<?php

declare(strict_types=1);

namespace Guave\DeeplBundle\DependencyInjection;

use Guave\DeeplBundle\Api\DeeplApi;
use Guave\DeeplBundle\Config\Config;
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

        $configDefinition = $container->getDefinition(Config::class);
        $configDefinition->setArgument(0, $mergedConfig);
    }
}
