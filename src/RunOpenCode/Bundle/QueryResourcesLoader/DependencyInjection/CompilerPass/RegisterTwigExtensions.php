<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register extensions for Twig.
 */
final class RegisterTwigExtensions implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasDefinition('runopencode.query_resources_loader.twig')) {
            return;
        }

        $definition = $container->getDefinition('runopencode.query_resources_loader.twig');

        // Extensions must always be registered before everything else.
        // For instance, global variable definitions must be registered
        // afterward. If not, the globals from the extensions will never
        // be registered.
        $calls             = $definition->getMethodCalls();
        $extensionServices = $container->findTaggedServiceIds('runopencode.query_resources_loader.twig.extension');

        $definition->setMethodCalls();

        foreach (\array_keys($extensionServices) as $id) {
            $definition->addMethodCall('addExtension', [new Reference($id)]);
        }

        $definition->setMethodCalls(\array_merge($definition->getMethodCalls(), $calls));
    }
}
