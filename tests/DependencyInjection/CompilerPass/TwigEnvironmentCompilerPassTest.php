<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\TwigEnvironmentCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class TwigEnvironmentCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     */
    public function itReRegistersExtensions(): void
    {
        $this->setDefinition('runopencode.query_resources_loader.twig', $twig = new Definition());
        $this->setDefinition('some_extension', $extension = new Definition());
        $extension->addTag('runopencode.query_resources_loader.twig.extension');
        $twig->addMethodCall('addExtension', [new Reference('some_extension')]);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('runopencode.query_resources_loader.twig', 'addExtension', [new Reference('some_extension')]);
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TwigEnvironmentCompilerPass());
    }
}
