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
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\ExecutorBuilderCompilerPass;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ExecutorBuilderCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @test
     */
    public function itRegistersDoctrineDbalExecutor()
    {
        $this->setDefinition('doctrine.dbal.default_connection', new Definition());
        $this->compile();

        $this->assertContainerBuilderHasService('run_open_code.query_resources_loader.executor.doctrine_dbal_default_connection_executor', DoctrineDbalExecutor::class);
        $this->assertContainerBuilderHasServiceDefinitionWithTag('run_open_code.query_resources_loader.executor.doctrine_dbal_default_connection_executor', 'run_open_code.query_resources_loader.executor', [
            'name' => 'doctrine_dbal_default_connection_executor'
        ]);
    }

    /**
     * @test
     */
    public function itDoesNotRegistersDoctrineDbalExecutor()
    {
        $this->setDefinition('some_dummy_definition', new Definition());

        $this->compile();
        $this->assertContainerBuilderNotHasService('run_open_code.query_resources_loader.executor.doctrine_dbal_default_connection_executor');
    }
    
    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new ExecutorBuilderCompilerPass());
    }
}
