<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass;

use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheMiddleware;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final readonly class ConfigureCacheMiddleware implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter(Extension::CACHE_POOL) || !$container->hasParameter(Extension::CACHE_DEFAULT_TTL)) {
            return;
        }

        /** @var string|null $pool */
        $pool = $container->getParameter(Extension::CACHE_POOL);
        /** @var int|null $ttl */
        $ttl = $container->getParameter(Extension::CACHE_DEFAULT_TTL);
        
        // nothing to reconfigure.
        if (null === $pool && null === $ttl) {
            return;
        }

        $container
            ->getDefinition(CacheMiddleware::class)
            ->setArgument('$cache', null !== $pool ? new Reference($pool) : null)
            ->setArgument('$defaultTtl', $ttl);
    }
}
