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
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\RegisterExecutorsCompilerPass;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterExecutorsCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     */
    public function itRegisterDefaultExecutor()
    {
        $this->setDefinition('runopencode.query_resources_loader', new Definition());

        $executor = new Definition(DoctrineDbalExecutor::class, []);
        $executor
            ->addTag('runopencode.query_resources_loader.executor', ['name' => 'dummy_executor']);

        $this->setDefinition('executor', $executor);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('runopencode.query_resources_loader', 'registerExecutor', [
            new Reference('executor'),
            'dummy_executor'
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('runopencode.query_resources_loader', 'registerExecutor', [
            new Reference('executor'),
            'default'
        ]);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\DependencyInjection\Exception\LogicException
     */
    public function itRequiresAtLeastOneExecutor()
    {
        $this->setDefinition('runopencode.query_resources_loader', new Definition());
        $this->compile();
    }

    /**
     * @test
     */
    public function itAllowsConfigurationOfDefaultExecutor()
    {
        $this->setDefinition('runopencode.query_resources_loader', new Definition());

        $executorOne = new Definition(DoctrineDbalExecutor::class, []);
        $executorOne
            ->addTag('runopencode.query_resources_loader.executor', ['name' => 'dummy_executor_one']);

        $this->setDefinition('executor_one', $executorOne);

        $executorTwo = new Definition(DoctrineDbalExecutor::class, []);
        $executorTwo
            ->addTag('runopencode.query_resources_loader.executor', ['name' => 'dummy_executor_two']);

        $this->setDefinition('executor_two', $executorTwo);

        $this->setParameter('runopencode.query_resources_loader.default_executor', 'executor_two');

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('runopencode.query_resources_loader', 'registerExecutor', [
            new Reference('executor_one'),
            'dummy_executor_one'
        ]);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('runopencode.query_resources_loader', 'registerExecutor', [
            new Reference('executor_two'),
            'dummy_executor_two'
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('runopencode.query_resources_loader', 'registerExecutor', [
            new Reference('executor_two'),
            'default'
        ]);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\DependencyInjection\Exception\LogicException
     */
    public function itContainsInvalidDefaultExecutorConfiguration()
    {
        $this->setDefinition('runopencode.query_resources_loader', new Definition());

        $executorOne = new Definition(DoctrineDbalExecutor::class, []);
        $executorOne
            ->addTag('runopencode.query_resources_loader.executor', ['name' => 'dummy_executor_one']);

        $this->setDefinition('executor_one', $executorOne);

        $executorTwo = new Definition(DoctrineDbalExecutor::class, []);
        $executorTwo
            ->addTag('runopencode.query_resources_loader.executor', ['name' => 'dummy_executor_two']);

        $this->setDefinition('executor_two', $executorTwo);

        $this->setParameter('runopencode.query_resources_loader.default_executor', 'non_existing');

        $this->compile();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RegisterExecutorsCompilerPass());
    }
}