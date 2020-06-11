<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\TwigExtensionsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class TwigExtensionsCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     */
    public function itRegistersTwigProfiler(): void
    {
        $this->setDefinition('twig.extension.profiler', new Definition());
        $this->setParameter('kernel.debug', true);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithTag('twig.extension.profiler', 'runopencode.query_resources_loader.twig.extension');
    }

    /**
     * @test
     */
    public function itRegistersTwigDebugger(): void
    {
        $this->setDefinition('twig.extension.debug', new Definition());
        $this->setParameter('kernel.debug', true);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithTag('twig.extension.debug', 'runopencode.query_resources_loader.twig.extension');
    }

    /**
     * @test
     */
    public function itIsProductionMode(): void
    {
        $this->setParameter('kernel.debug', false);

        $this->compile();

        $this->assertContainerBuilderNotHasService('twig.extension.debug');
        $this->assertContainerBuilderNotHasService('twig.extension.profiler');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TwigExtensionsCompilerPass());
    }
}
