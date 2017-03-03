<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\TwigLoaderCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class TwigLoaderCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     * @expectedException \Symfony\Component\DependencyInjection\Exception\LogicException
     */
    public function itHasNoLoader()
    {
        $this->setDefinition('run_open_code.query_resources_loader.twig', new Definition());
        $this->compile();
    }

    /**
     * @test
     */
    public function itHasOneLoader()
    {
        $this->setDefinition('run_open_code.query_resources_loader.twig', new Definition());
        $this->setDefinition('dummy_loader', $loader = new Definition());
        $loader
            ->addTag('run_open_code.query_resources_loader.twig.loader');

        $this->compile();

        $this
            ->assertContainerBuilderHasAlias('run_open_code.query_resources_loader.twig.loader', 'dummy_loader');
    }

    /**
     * @test
     */
    public function itHasPrioritizedLoaders()
    {
        $this->setDefinition('run_open_code.query_resources_loader.twig', new Definition());
        $this->setDefinition('run_open_code.query_resources_loader.twig.loader.chain', new Definition());

        $this->setDefinition('dummy_loader_1', $dummy_loader_1 = new Definition());
        $dummy_loader_1
            ->addTag('run_open_code.query_resources_loader.twig.loader', ['priority' => 100]);

        $this->setDefinition('dummy_loader_2', $dummy_loader_2 = new Definition());
        $dummy_loader_2
            ->addTag('run_open_code.query_resources_loader.twig.loader', ['priority' => 300]);

        $this->setDefinition('dummy_loader_3', $dummy_loader_3 = new Definition());
        $dummy_loader_3
            ->addTag('run_open_code.query_resources_loader.twig.loader', ['priority' => 200]);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('run_open_code.query_resources_loader.twig.loader.chain', 'addLoader', [ new Reference('dummy_loader_1') ]);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('run_open_code.query_resources_loader.twig.loader.chain', 'addLoader', [ new Reference('dummy_loader_2') ]);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('run_open_code.query_resources_loader.twig.loader.chain', 'addLoader', [ new Reference('dummy_loader_3')  ]);

        $this
            ->assertContainerBuilderHasAlias('run_open_code.query_resources_loader.twig.loader', 'run_open_code.query_resources_loader.twig.loader.chain');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new TwigLoaderCompilerPass());
    }
}