<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheMiddleware;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\ConfigureCacheMiddleware;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class ConfigureCacheMiddlewareTest extends AbstractCompilerPassTestCase
{
    public function testItReconfiguresMiddleware(): void
    {
        $this->setParameter(Extension::CACHE_POOL, 'foo');
        $this->setParameter(Extension::CACHE_DEFAULT_TTL, 3600);
        $this->setDefinition(CacheMiddleware::class, new Definition(CacheMiddleware::class, [null, null]));

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            CacheMiddleware::class,
            '$cache',
            'foo'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            CacheMiddleware::class,
            '$defaultTtl',
            3600
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ConfigureCacheMiddleware());
    }
}
