<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass;

use RunOpenCode\Bundle\QueryResourcesLoader\Manager\DefaultManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * Registers query executors.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
final class RegisterExecutorsCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(DefaultManager::class)) {
            return;
        }

        $definition     = $container->findDefinition(DefaultManager::class);
        $taggedServices = $container->findTaggedServiceIds('runopencode.query_resources_loader.executor');
        $executors      = [];

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('registerExecutor', [
                    new Reference($id),
                    $id,
                ]);

                $executors[$id] = $id;

                if (isset($attributes['alias'])) {
                    $definition->addMethodCall('registerExecutor', [
                        new Reference($id),
                        $attributes['alias'],
                    ]);

                    $executors[$attributes['alias']] = $id;
                }
            }
        }

        if (0 === \count($executors)) {
            throw new LogicException('At least one query executor is required to be registered, none found.');
        }

        /** @var string $defaultExecutor */
        $defaultExecutor = ($container->hasParameter('runopencode.query_resources_loader.default_executor')
            ? $container->getParameter('runopencode.query_resources_loader.default_executor')
            : \array_values($executors)[0]);

        if (!$container->hasDefinition($defaultExecutor)) {
            throw new LogicException(\sprintf(
                'Default query executor "%s" can not be found.',
                $defaultExecutor
            ));
        }

        $definition->addMethodCall('registerExecutor', [
            new Reference($defaultExecutor),
            'default',
        ]);
    }
}
