<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Process Twig loaders.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
final class TwigLoaderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('runopencode.query_resources_loader.twig')) {
            return;
        }

        // register additional Query loaders
        $loaderIds = $container->findTaggedServiceIds('runopencode.query_resources_loader.twig.loader');

        if (0 === \count($loaderIds)) {
            throw new LogicException('No twig loaders found. You need to tag at least one loader with "runopencode.query_resources_loader.twig.loader"');
        }

        if (1 === \count($loaderIds)) {
            $container->setAlias('runopencode.query_resources_loader.twig.loader', (string)\key($loaderIds));
            return;
        }

        $chainLoader        = $container->getDefinition('runopencode.query_resources_loader.twig.loader.chain');
        $prioritizedLoaders = [];

        foreach ($loaderIds as $id => $tags) {
            foreach ($tags as $tag) {
                $priority                        = $tag['priority'] ?? 0;
                $prioritizedLoaders[$priority][] = $id;
            }
        }

        \krsort($prioritizedLoaders);

        foreach ($prioritizedLoaders as $loaders) {
            foreach ($loaders as $loader) {
                $chainLoader->addMethodCall('addLoader', [new Reference($loader)]);
            }
        }

        $container->setAlias('runopencode.query_resources_loader.twig.loader', 'runopencode.query_resources_loader.twig.loader.chain');
    }
}
