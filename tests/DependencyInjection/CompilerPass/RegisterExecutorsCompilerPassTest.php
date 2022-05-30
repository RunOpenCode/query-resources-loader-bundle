<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\RegisterExecutorsCompilerPass;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutor;
use RunOpenCode\Bundle\QueryResourcesLoader\Manager\DefaultManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterExecutorsCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     */
    public function itRegisterDefaultExecutor(): void
    {
        $this->setDefinition(DefaultManager::class, new Definition());

        $executor = new Definition(DoctrineDbalExecutor::class, []);

        $executor->addTag('runopencode.query_resources_loader.executor', ['alias' => 'dummy_executor']);
        $this->setDefinition('executor', $executor);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(DefaultManager::class, 'registerExecutor', [
            new Reference('executor'),
            'dummy_executor',
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(DefaultManager::class, 'registerExecutor', [
            new Reference('executor'),
            'default',
        ]);
    }

    /**
     * @test
     */
    public function itRequiresAtLeastOneExecutor(): void
    {
        $this->setDefinition(DefaultManager::class, new Definition());

        $this->expectException(LogicException::class);

        $this->compile();
    }

    /**
     * @test
     */
    public function itAllowsConfigurationOfDefaultExecutor(): void
    {
        $this->setDefinition(DefaultManager::class, new Definition());

        $executorOne = new Definition(DoctrineDbalExecutor::class, []);

        $executorOne->addTag('runopencode.query_resources_loader.executor', ['alias' => 'dummy_executor_one']);
        $this->setDefinition('executor_one', $executorOne);

        $executorTwo = new Definition(DoctrineDbalExecutor::class, []);

        $executorTwo->addTag('runopencode.query_resources_loader.executor', ['alias' => 'dummy_executor_two']);
        $this->setDefinition('executor_two', $executorTwo);

        $this->setParameter('runopencode.query_resources_loader.default_executor', 'executor_two');

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(DefaultManager::class, 'registerExecutor', [
            new Reference('executor_one'),
            'dummy_executor_one',
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(DefaultManager::class, 'registerExecutor', [
            new Reference('executor_two'),
            'dummy_executor_two',
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(DefaultManager::class, 'registerExecutor', [
            new Reference('executor_two'),
            'default',
        ]);
    }

    /**
     * @test
     */
    public function itContainsInvalidDefaultExecutorConfiguration(): void
    {
        $this->setDefinition(DefaultManager::class, new Definition());

        $executorOne = new Definition(DoctrineDbalExecutor::class, []);

        $executorOne->addTag('runopencode.query_resources_loader.executor', ['name' => 'dummy_executor_one']);
        $this->setDefinition('executor_one', $executorOne);

        $executorTwo = new Definition(DoctrineDbalExecutor::class, []);

        $executorTwo->addTag('runopencode.query_resources_loader.executor', ['name' => 'dummy_executor_two']);
        $this->setDefinition('executor_two', $executorTwo);
        $this->setParameter('runopencode.query_resources_loader.default_executor', 'non_existing');

        $this->expectException(LogicException::class);

        $this->compile();
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterExecutorsCompilerPass());
    }
}
