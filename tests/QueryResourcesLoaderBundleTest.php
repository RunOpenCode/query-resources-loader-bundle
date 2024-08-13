<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\ConfigureCacheMiddleware;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\RegisterDbalExecutors;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\RegisterTwigExtensions;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Extension;
use RunOpenCode\Bundle\QueryResourcesLoader\QueryResourcesLoaderBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class QueryResourcesLoaderBundleTest extends TestCase
{
    public function testItRegistersExtension(): void
    {
        $this->assertInstanceOf(Extension::class, $this->getBundle()->getContainerExtension());
    }

    public function testItRegistersCompilerPasses(): void
    {
        $bundle   = $this->getBundle();
        $compiler = new ContainerBuilder();

        $bundle->build($compiler);

        $passConfig = $compiler->getCompiler()->getPassConfig();

        $passes = \array_filter(\array_map(function(CompilerPassInterface $compilerPass) {
            $class = \get_class($compilerPass);

            if (\str_starts_with($class, 'RunOpenCode')) {
                return $class;
            }

            return null;
        }, $passConfig->getBeforeOptimizationPasses()));

        $expected = [
            ConfigureCacheMiddleware::class,
            RegisterTwigExtensions::class,
            RegisterDbalExecutors::class,
        ];

        \sort($passes);
        \sort($expected);

        $this->assertEquals($expected, $passes);
    }

    private function getBundle(): QueryResourcesLoaderBundle
    {
        return new QueryResourcesLoaderBundle();
    }
}
