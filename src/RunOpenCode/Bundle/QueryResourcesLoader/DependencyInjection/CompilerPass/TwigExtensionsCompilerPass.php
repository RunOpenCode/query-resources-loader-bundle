<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Prepares Twig extensions.
 */
final class TwigExtensionsCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('kernel.debug') || !$container->getParameter('kernel.debug')) {
            return;
        }

        if ($container->hasDefinition('twig.extension.profiler')) {
            $container->getDefinition('twig.extension.profiler')->addTag('runopencode.query_resources_loader.twig.extension');
        }

        if ($container->hasDefinition('twig.extension.debug')) {
            $container->getDefinition('twig.extension.debug')->addTag('runopencode.query_resources_loader.twig.extension');
        }
    }
}
