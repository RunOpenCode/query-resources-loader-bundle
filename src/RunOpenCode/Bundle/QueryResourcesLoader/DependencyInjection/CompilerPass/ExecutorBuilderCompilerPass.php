<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass;

use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Builds default query executor for Doctrine Dbal default connection (if possible).
 */
final class ExecutorBuilderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('doctrine.dbal.default_connection')) {
            return;
        }

        $definition = new Definition();

        $definition->setClass(DoctrineDbalExecutor::class);
        $definition->setArguments([
            new Reference('doctrine.dbal.default_connection'),
            $container->hasParameter('kernel.debug') ? $container->getParameter('kernel.debug') : false,
        ]);
        $definition->setPublic(false);

        $definition->addTag('runopencode.query_resources_loader.executor', [
            'name' => 'doctrine_dbal_default_connection_executor',
        ]);

        $container->setDefinition('runopencode.query_resources_loader.executor.doctrine_dbal_default_connection_executor', $definition);
    }
}
