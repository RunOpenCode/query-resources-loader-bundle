<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\TwigEnvironmentCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class TwigEnvironmentCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     */
    public function itReRegistersExtensions()
    {
        $this->setDefinition('run_open_code.query_resources_loader.twig', $twig = new Definition());

        $this->setDefinition('some_extension', $extension = new Definition());
        $extension
            ->addTag('run_open_code.query_resources_loader.twig.extension');

        $twig
            ->addMethodCall('addExtension', [ new Reference('some_extension') ]);

        $this->compile();

        $this
            ->assertContainerBuilderHasServiceDefinitionWithMethodCall('run_open_code.query_resources_loader.twig', 'addExtension', [ new Reference('some_extension') ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new TwigEnvironmentCompilerPass());
    }
}
