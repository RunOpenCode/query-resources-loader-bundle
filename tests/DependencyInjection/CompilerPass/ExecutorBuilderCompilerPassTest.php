<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\ExecutorBuilderCompilerPass;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class ExecutorBuilderCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     */
    public function itRegistersDoctrineDbalExecutor(): void
    {
        $this->setDefinition('doctrine.dbal.default_connection', new Definition());
        $this->compile();

        $this->assertContainerBuilderHasService('runopencode.query_resources_loader.executor.doctrine_dbal_default_connection_executor', DoctrineDbalExecutor::class);
        $this->assertContainerBuilderHasServiceDefinitionWithTag('runopencode.query_resources_loader.executor.doctrine_dbal_default_connection_executor', 'runopencode.query_resources_loader.executor', [
            'name' => 'doctrine_dbal_default_connection_executor',
        ]);
    }

    /**
     * @test
     */
    public function itDoesNotRegistersDoctrineDbalExecutor(): void
    {
        $this->setDefinition('some_dummy_definition', new Definition());

        $this->compile();
        $this->assertContainerBuilderNotHasService('runopencode.query_resources_loader.executor.doctrine_dbal_default_connection_executor');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ExecutorBuilderCompilerPass());
    }
}
