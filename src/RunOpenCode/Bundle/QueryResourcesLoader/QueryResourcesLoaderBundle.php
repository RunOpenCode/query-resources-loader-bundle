<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader;

use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\ConfigureCacheMiddleware;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\ConfigureDefaultLoader;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\RegisterDbalExecutors;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\RegisterTwigExtensions;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class QueryResourcesLoaderBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension(): ExtensionInterface
    {
        return new Extension();
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        // Register Twig related compiler passes.
        $container->addCompilerPass(new RegisterTwigExtensions());

        // Register executor compiler passes.
        $container->addCompilerPass(new RegisterDbalExecutors());

        // Register middleware configuration compiler passes.
        $container->addCompilerPass(new ConfigureCacheMiddleware());

        // Register loader compiler passes.
        $container->addCompilerPass(new ConfigureDefaultLoader());
    }
}
