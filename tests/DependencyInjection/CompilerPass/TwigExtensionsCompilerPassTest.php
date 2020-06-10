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
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\TwigExtensionsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class TwigExtensionsCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     */
    public function itRegistersTwigProfiler()
    {
        $this->setDefinition('twig.extension.profiler', new Definition());
        $this->setParameter('kernel.debug', true);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithTag('twig.extension.profiler', 'runopencode.query_resources_loader.twig.extension');
    }

    /**
     * @test
     */
    public function itRegistersTwigDebugger()
    {
        $this->setDefinition('twig.extension.debug', new Definition());
        $this->setParameter('kernel.debug', true);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithTag('twig.extension.debug', 'runopencode.query_resources_loader.twig.extension');
    }

    /**
     * @test
     */
    public function itIsProductionMode()
    {
        $this->setParameter('kernel.debug', false);

        $this->compile();

        $this->assertContainerBuilderNotHasService('twig.extension.debug');
        $this->assertContainerBuilderNotHasService('twig.extension.profiler');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new TwigExtensionsCompilerPass());
    }
}