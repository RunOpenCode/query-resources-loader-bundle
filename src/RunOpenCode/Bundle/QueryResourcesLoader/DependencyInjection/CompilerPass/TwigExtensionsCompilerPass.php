<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class TwigExtensionsCompilerPass
 *
 * Prepares Twig extensions.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass
 */
class TwigExtensionsCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('kernel.debug') || !$container->getParameter('kernel.debug')) {
            return;
        }

        if ($container->hasDefinition('twig.extension.profiler')) {
            $container->getDefinition('twig.extension.profiler')->addTag('run_open_code.query_resources_loader.twig.extension');
        }

        if ($container->hasDefinition('twig.extension.debug')) {
            $container->getDefinition('twig.extension.debug')->addTag('run_open_code.query_resources_loader.twig.extension');
        }
    }
}
