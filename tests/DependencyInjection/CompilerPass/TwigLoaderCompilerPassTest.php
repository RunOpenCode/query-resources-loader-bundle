<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\TwigLoaderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

final class TwigLoaderCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     */
    public function itHasNoLoader(): void
    {
        $this->setDefinition('runopencode.query_resources_loader.twig', new Definition());

        $this->expectException(LogicException::class);

        $this->compile();
    }

    /**
     * @test
     */
    public function itHasOneLoader(): void
    {
        $this->setDefinition('runopencode.query_resources_loader.twig', new Definition());
        $this->setDefinition('dummy_loader', $loader = new Definition());

        $loader->addTag('runopencode.query_resources_loader.twig.loader');

        $this->compile();

        $this->assertContainerBuilderHasAlias('runopencode.query_resources_loader.twig.loader', 'dummy_loader');
    }

    /**
     * @test
     */
    public function itHasPrioritizedLoaders(): void
    {
        $this->setDefinition('runopencode.query_resources_loader.twig', new Definition());
        $this->setDefinition('runopencode.query_resources_loader.twig.loader.chain', new Definition());

        $this->setDefinition('dummy_loader_1', $dummy_loader_1 = new Definition());
        $dummy_loader_1->addTag('runopencode.query_resources_loader.twig.loader', ['priority' => 100]);

        $this->setDefinition('dummy_loader_2', $dummy_loader_2 = new Definition());
        $dummy_loader_2->addTag('runopencode.query_resources_loader.twig.loader', ['priority' => 300]);

        $this->setDefinition('dummy_loader_3', $dummy_loader_3 = new Definition());
        $dummy_loader_3->addTag('runopencode.query_resources_loader.twig.loader', ['priority' => 200]);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('runopencode.query_resources_loader.twig.loader.chain', 'addLoader', [ new Reference('dummy_loader_1') ]);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('runopencode.query_resources_loader.twig.loader.chain', 'addLoader', [ new Reference('dummy_loader_2') ]);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('runopencode.query_resources_loader.twig.loader.chain', 'addLoader', [ new Reference('dummy_loader_3')  ]);

        $this->assertContainerBuilderHasAlias('runopencode.query_resources_loader.twig.loader', 'runopencode.query_resources_loader.twig.loader.chain');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TwigLoaderCompilerPass());
    }
}
