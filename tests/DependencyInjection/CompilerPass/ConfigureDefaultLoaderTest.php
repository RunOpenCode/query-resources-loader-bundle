<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\LoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\ConfigureDefaultLoader;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Extension;
use RunOpenCode\Bundle\QueryResourcesLoader\Loader\RawLoader;
use RunOpenCode\Bundle\QueryResourcesLoader\Loader\TwigLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class ConfigureDefaultLoaderTest extends AbstractCompilerPassTestCase
{
    public function testItConfiguresRawLoader(): void
    {
        $this->setParameter(Extension::DEFAULT_LOADER, 'raw');
        $this->setDefinition(RawLoader::class, new Definition(RawLoader::class));

        $this->compile();

        $this->assertContainerBuilderHasAlias(LoaderInterface::class, RawLoader::class);
    }

    public function testItConfiguresTwigLoader(): void
    {
        $this->setParameter(Extension::DEFAULT_LOADER, 'twig');
        $this->setDefinition(TwigLoader::class, new Definition(TwigLoader::class));

        $this->compile();

        $this->assertContainerBuilderHasAlias(LoaderInterface::class, TwigLoader::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ConfigureDefaultLoader());
    }
}
