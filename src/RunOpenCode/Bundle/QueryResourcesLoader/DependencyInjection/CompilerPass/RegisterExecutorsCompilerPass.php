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
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\LogicException;

class RegisterExecutorsCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('run_open_code.query_resources_loader')) {

            $definition = $container->getDefinition('run_open_code.query_resources_loader');
            $executors = array();

            foreach ($container->findTaggedServiceIds('run_open_code.query_resources_loader.executor') as $id => $tags) {

                foreach ($tags as $attributes) {

                    $definition->addMethodCall('registerExecutor', array(
                            new Reference($id),
                            $attributes['name']
                    ));

                    $executors[$attributes['name']] = $id;
                }
            }

            if (0 === count($executors)) {
                throw new LogicException('At least one query executor is required to be registered, none found.');
            }

            if (
                $container->hasParameter('run_open_code.query_resources_loader.default_executor')
                &&
                null !== $container->getParameter('run_open_code.query_resources_loader.default_executor')
            ) {
                $defaultExecutor = $container->getParameter('run_open_code.query_resources_loader.default_executor');
            } else {
                $executors = array_values($executors);
                $defaultExecutor = $executors[0];
            }

            if (!$container->hasDefinition($defaultExecutor)) {
                throw new LogicException(sprintf('Default query executor "%s" can not be found.', $defaultExecutor));
            }

            $definition->addMethodCall('registerExecutor', array(
                new Reference($defaultExecutor),
                'default'
            ));
        }
    }
}
