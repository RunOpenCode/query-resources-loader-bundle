<?php
/*
 * This file is part of the QueryResourcesLoader Bundle, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass;

use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ExecutorBuilderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('doctrine.dbal.default_connection')) {

            $definition = new Definition();

            $definition
                ->setClass(DoctrineDbalExecutor::class)
                ->setArguments(array(new Reference('doctrine.dbal.default_connection')))
                ->setPublic(false)
            ;

            $definition->addTag('run_open_code.query_resources_loader.executor', array(
                'name' => 'doctrine_dbal_default_connection_executor'
            ));

            $container->setDefinition('run_open_code.query_resources_loader.executor.doctrine_dbal_default_connection_executor', $definition);
        }
    }
}
