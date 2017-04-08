<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Extension;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Fixtures\Bundles\BarBundle\BarBundle;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Fixtures\Bundles\FooBundle\FooBundle;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @test
     */
    public function itSetsDefaultExecutor()
    {
        $this->setParameter('kernel.bundles', []);

        $this->load(['default_executor' => 'some_default_executor']);

        $this->assertContainerBuilderHasParameter('runopencode.query_resources_loader.default_executor', 'some_default_executor');
    }

    /**
     * @test
     */
    public function itConfiguresTwigAutoescape()
    {
        $this->setParameter('kernel.bundles', []);
        $this->setDefinition('runopencode.query_resources_loader.twig', new Definition());

        $this->load(['twig' => ['autoescape_service' => 'autoescape_service', 'autoescape_service_method' => 'autoescape_service_method']]);

        $arguments = $this->container->getDefinition('runopencode.query_resources_loader.twig')->getArgument(1);

        $this->assertInstanceOf(Reference::class, $arguments['autoescape'][0]);
        $this->assertEquals('autoescape_service', (string) $arguments['autoescape'][0]);
        $this->assertEquals('autoescape_service_method', $arguments['autoescape'][1]);
    }

    /**
     * @test
     */
    public function itConfiguresTwigGlobals()
    {
        $globals = array(
            'array' => array(),
            'false' => false,
            'float' => 2.0,
            'integer' => 3,
            'null' => null,
            'object' => new \stdClass(),
            'string' => 'foo',
            'true' => true,
        );

        $this->setParameter('kernel.bundles', []);
        $this->setDefinition('runopencode.query_resources_loader.twig', new Definition());

        $this->load(['twig' => ['globals' => $globals]]);

        $calls = $this->container->getDefinition('runopencode.query_resources_loader.twig')->getMethodCalls();

        foreach ($calls as $call) {
            $this->assertEquals('addGlobal', $call[0]);
            $this->assertSame($globals[$call[1][0]], $call[1][1]);
        }
    }

    /**
     * @test
     */
    public function itConfiguresServicesAsTwigGlobals()
    {
        $this->setParameter('kernel.bundles', []);
        $this->setDefinition('runopencode.query_resources_loader.twig', new Definition());

        $this->load(['twig' => [ 'globals' => [ 'some_service' => [ 'type' => 'service', 'id' => 'service_id' ] ] ] ]);

        $call = $this->container->getDefinition('runopencode.query_resources_loader.twig')->getMethodCalls()[0];

        $this->assertEquals('addGlobal', $call[0]);
        $this->assertEquals('some_service', $call[1][0]);
        $this->assertInstanceOf(Reference::class, $call[1][1]);
        $this->assertEquals('service_id', (string) $call[1][1]);
    }

    /**
     * @test
     */
    public function itConfiguresTwigResourcePaths()
    {
        $this->setParameter('kernel.bundles', []);
        $this->setDefinition('runopencode.query_resources_loader.twig', new Definition());
        $this->setDefinition('runopencode.query_resources_loader.twig.loader.filesystem', new Definition());

        $this->load(['twig' => [ 'paths' => [
            'path1' => '',
            'path2' => '',
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
            ['path1'],
            ['path2'],
            ['namespaced_path1', 'namespace1'],
            ['namespaced_path2', 'namespace2'],
            ['namespaced_path3', 'namespace3'],
        ], $paths);
    }

    /**
     * @test
     */
    public function itConfiguresTwigBundlePaths()
    {
        $this->setParameter('kernel.bundles', [
            'FooBundle' => FooBundle::class,
            'BarBundle' => BarBundle::class
        ]);
        
        $this->setParameter('kernel.root_dir', realpath(__DIR__ . '/../Fixtures/app'));
        $this->setDefinition('runopencode.query_resources_loader.twig.loader.filesystem', new Definition());

        $this->load();

        $calls = $this->container->getDefinition('runopencode.query_resources_loader.twig.loader.filesystem')->getMethodCalls();

        $paths = [];

        foreach ($calls as $call) {
            if ('addPath' === $call[0]) {
                $paths[] = $call[1];
            }
        }

        $this->assertEquals([
            [ realpath(__DIR__ . '/../Fixtures/app/Resources/FooBundle/query'), 'Foo' ],
            [ realpath(__DIR__ . '/../Fixtures/Bundles/FooBundle/Resources/query'), 'Foo' ],
            [ realpath(__DIR__ . '/../Fixtures/Bundles/BarBundle/Resources/query'), 'Bar' ],
        ], $paths);
    }

    /**
     * @test
     */
    public function itConfiguresTwigWarmUpCommand()
    {
        $this->setParameter('kernel.bundles', []);
        $this->setDefinition('runopencode.query_resources_loader.twig', new Definition());
        $this->setDefinition('runopencode.query_resources_loader.twig.loader.filesystem', new Definition());

        $this->load(['twig' => [ 'paths' => [
            'path1' => '',
            'namespaced_path1' => 'namespace1'
        ]]]);

        $calls = $this->container->getDefinition('runopencode.query_resources_loader.twig.loader.filesystem')->getMethodCalls();

        $paths = [];

        foreach ($calls as $call) {
            if ('addPath' === $call[0]) {
                $paths[] = $call[1];
            }
        }

        $this->container->getDefinition('runopencode.query_resources_loader.twig.query_sources_iterator')->getArgument(2);
        
        $this->assertEquals([
            ['path1'],
            ['namespaced_path1', 'namespace1'],
        ], $paths);
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return [ new Extension() ];
    }
}
