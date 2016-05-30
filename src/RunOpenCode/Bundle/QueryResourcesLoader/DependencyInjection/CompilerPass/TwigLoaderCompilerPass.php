<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

class TwigLoaderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('run_open_code.query_resources_loader.twig')) {
            return;
        }

        // register additional Query loaders
        $loaderIds = $container->findTaggedServiceIds('run_open_code.query_resources_loader.twig.loader');

        if (count($loaderIds) === 0) {
            throw new LogicException('No twig loaders found. You need to tag at least one loader with "run_open_code.query_resources_loader.twig.loader"');
        }

        if (count($loaderIds) === 1) {
            $container->setAlias('run_open_code.query_resources_loader.twig.loader', key($loaderIds));
        } else {
            $chainLoader = $container->getDefinition('run_open_code.query_resources_loader.twig.loader.chain');

            $prioritizedLoaders = array();

            foreach ($loaderIds as $id => $tags) {
                foreach ($tags as $tag) {
                    $priority = isset($tag['priority']) ? $tag['priority'] : 0;
                    $prioritizedLoaders[$priority][] = $id;
                }
            }

            krsort($prioritizedLoaders);

            foreach ($prioritizedLoaders as $loaders) {
                foreach ($loaders as $loader) {
                    $chainLoader->addMethodCall('addLoader', array(new Reference($loader)));
                }
            }

            $container->setAlias('run_open_code.query_resources_loader.twig.loader', 'run_open_code.query_resources_loader.twig.loader.chain');
        }
    }
}
