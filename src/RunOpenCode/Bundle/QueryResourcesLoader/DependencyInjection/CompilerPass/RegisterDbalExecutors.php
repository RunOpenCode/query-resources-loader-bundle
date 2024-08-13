<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass;

use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Extension;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DoctrineDbalQueryExecutor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Builds query executors for Doctrine Dbal connections (if possible).
 */
final readonly class RegisterDbalExecutors implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     *
     * @psalm-suppress UnnecessaryVarAnnotation
     */
    public function process(ContainerBuilder $container): void
    {
        if (
            !$container->hasParameter('doctrine.connections')
            ||
            !$container->hasParameter(Extension::DEFAULT_EXECUTOR)
        ) {
            return; // doctrine/dbal is not installed, or default executor is not configured.
        }

        /** @var string $defaultConnection */
        $defaultConnection = $container->getParameter('doctrine.default_connection');
        /** @var string $defaultExecutor */
        $defaultExecutor = $container->getParameter(Extension::DEFAULT_EXECUTOR) ?? \sprintf('doctrine.dbal.%s_connection', $defaultConnection);
        /** @var string[] $connections */
        $connections = $container->getParameter('doctrine.connections');

        foreach ($connections as $connection) {
            $id         = \sprintf('runopencode.query_resources_loader.executor.%s', $connection);
            $definition = new Definition(DoctrineDbalQueryExecutor::class, [
                new Reference($connection),
                $connection,
            ]);

            $definition->addTag('runopencode.query_resources_loader.executor', [
                'label'    => $connection,
                'priority' => $defaultExecutor === $connection || $defaultExecutor === $id ? 1000 : 0,
            ]);

            $container->setDefinition($id, $definition);
        }
    }
}
