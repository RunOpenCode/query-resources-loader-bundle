<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Extension;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Resources\bundles\BarBundle\BarBundle;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Resources\bundles\FooBundle\FooBundle;
use RunOpenCode\Bundle\QueryResourcesLoader\Twig\CacheWarmer\QuerySourcesIterator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ExtensionTest extends AbstractExtensionTestCase
{
    public function testItSetsDefaultExecutor(): void
    {
        $this->setParameter('kernel.bundles', []);

        $this->load(['default_executor' => 'some_default_executor']);

        $this->assertContainerBuilderHasParameter('runopencode.query_resources_loader.default_executor', 'some_default_executor');
    }

    public function testItConfiguresTwigAutoescape(): void
    {
        $this->setParameter('kernel.bundles', []);
        $this->setDefinition('runopencode.query_resources_loader.twig', new Definition());

        $this->load(['twig' => ['autoescape_service' => 'autoescape_service', 'autoescape_service_method' => 'autoescape_service_method']]);

        /** @var array<string, mixed[]> $arguments */
        $arguments = $this->container->getDefinition('runopencode.query_resources_loader.twig')->getArgument(1);

        $this->assertInstanceOf(Reference::class, $arguments['autoescape'][0] ?? null);
        $this->assertEquals('autoescape_service', $arguments['autoescape'][0]);
        $this->assertEquals('autoescape_service_method', $arguments['autoescape'][1] ?? null);
    }

    public function testItConfiguresTwigGlobals(): void
    {
        $globals = [
            'array'   => [],
            'false'   => false,
            'float'   => 2.0,
            'integer' => 3,
            'null'    => null,
            'object'  => new \stdClass(),
            'string'  => 'foo',
            'true'    => true,
        ];

        $this->setParameter('kernel.bundles', []);
        $this->setDefinition('runopencode.query_resources_loader.twig', new Definition());

        $this->load(['twig' => ['globals' => $globals]]);

        $calls = $this->container->getDefinition('runopencode.query_resources_loader.twig')->getMethodCalls();

        foreach ($calls as $call) {
            $this->assertEquals('addGlobal', $call[0]);
            $this->assertSame($globals[$call[1][0]], $call[1][1]);
        }
    }

    public function testItConfiguresServicesAsTwigGlobals(): void
    {
        $this->setParameter('kernel.bundles', []);
        $this->setDefinition('runopencode.query_resources_loader.twig', new Definition());

        $this->load(['twig' => ['globals' => ['some_service' => ['type' => 'service', 'id' => 'service_id']]]]);

        $call = $this->container->getDefinition('runopencode.query_resources_loader.twig')->getMethodCalls()[0];

        $this->assertEquals('addGlobal', $call[0]);
        $this->assertEquals('some_service', $call[1][0]);
        $this->assertInstanceOf(Reference::class, $call[1][1]);
        $this->assertEquals('service_id', (string)$call[1][1]);
    }

    public function testItConfiguresTwigResourcePaths(): void
    {
        $this->setParameter('kernel.bundles', []);
        $this->setDefinition('runopencode.query_resources_loader.twig', new Definition());
        $this->setDefinition('runopencode.query_resources_loader.twig.loader.filesystem', new Definition());

        $this->load(['twig' => ['paths' => [
            'path1'            => '',
            'path2'            => '',
            'namespaced_path1' => 'namespace1',
            'namespaced_path2' => 'namespace2',
            'namespaced_path3' => 'namespace3',
        ]]]);

        $calls = $this->container->getDefinition('runopencode.query_resources_loader.twig.loader.filesystem')->getMethodCalls();

        $paths = [];

        foreach ($calls as $call) {
            if ('addPath' === $call[0]) {
                $paths[] = $call[1];
            }
        }

        $this->assertEquals([
            [$this->container->getParameter('runopencode.query_resources_loader.default_path'), '__main__'],
            ['path1'],
            ['path2'],
            ['namespaced_path1', 'namespace1'],
            ['namespaced_path2', 'namespace2'],
            ['namespaced_path3', 'namespace3'],
        ], $paths);
    }

    public function testItConfiguresTwigBundlePaths(): void
    {
        $this->setParameter('kernel.bundles', [
            'FooBundle' => FooBundle::class,
            'BarBundle' => BarBundle::class,
        ]);

        $this->setDefinition('runopencode.query_resources_loader.twig.loader.filesystem', new Definition());

        $this->load();

        $paths = [];
        $calls = $this->container->getDefinition('runopencode.query_resources_loader.twig.loader.filesystem')->getMethodCalls();

        foreach ($calls as $call) {
            if ('addPath' === $call[0]) {
                $paths[] = $call[1];
            }
        }

        $this->assertEquals([
            [$this->container->getParameter('runopencode.query_resources_loader.default_path'), '__main__'],
            [\realpath(__DIR__ . '/../Resources/App/query/bundles/FooBundle'), 'Foo'],
            [\realpath(__DIR__ . '/../Resources/bundles/FooBundle/Resources/query'), 'Foo'],
            [\realpath(__DIR__ . '/../Resources/bundles/BarBundle/Resources/query'), 'Bar'],
        ], $paths);
    }

    public function testItConfiguresTwigWarmUpCommand(): void
    {
        $this->setParameter('kernel.bundles', []);
        $this->setDefinition('runopencode.query_resources_loader.twig', new Definition());
        $this->setDefinition('runopencode.query_resources_loader.twig.loader.filesystem', new Definition());

        $this->load(['twig' => ['paths' => [
            'path1'            => '',
            'namespaced_path1' => 'namespace1',
        ]]]);

        $calls = $this->container->getDefinition('runopencode.query_resources_loader.twig.loader.filesystem')->getMethodCalls();

        $paths = [];

        foreach ($calls as $call) {
            if ('addPath' === $call[0]) {
                $paths[] = $call[1];
            }
        }

        $this->container->getDefinition(QuerySourcesIterator::class)->getArgument(2);

        $this->assertEquals([
            [$this->container->getParameter('runopencode.query_resources_loader.default_path'), '__main__'],
            ['path1'],
            ['namespaced_path1', 'namespace1'],
        ], $paths);
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions(): array
    {
        $this->setParameter('kernel.project_dir', \realpath(__DIR__ . '/../Resources/App'));

        return [
            new Extension(),
        ];
    }
}
