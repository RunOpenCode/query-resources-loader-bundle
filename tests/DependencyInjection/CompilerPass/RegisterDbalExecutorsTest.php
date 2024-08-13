<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\DependencyInjection\CompilerPass;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\RegisterDbalExecutors;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DoctrineDbalQueryExecutor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegisterDbalExecutorsTest extends AbstractCompilerPassTestCase
{
    public function testItRegisterDbalExecutors(): void
    {
        $this->setParameter('doctrine.connections', ['doctrine.dbal.foo_connection', 'doctrine.dbal.bar_connection']);
        $this->setParameter('doctrine.default_connection', 'bar');
        $this->setParameter('runopencode.query_resources_loader.default_executor', null);

        $this->compile();

        $this->assertContainerBuilderHasService('runopencode.query_resources_loader.executor.doctrine.dbal.foo_connection', DoctrineDbalQueryExecutor::class);
        $this->assertContainerBuilderHasService('runopencode.query_resources_loader.executor.doctrine.dbal.bar_connection', DoctrineDbalQueryExecutor::class);

        $this->assertContainerBuilderHasServiceDefinitionWithTag('runopencode.query_resources_loader.executor.doctrine.dbal.bar_connection', 'runopencode.query_resources_loader.executor', [
            'label'    => 'doctrine.dbal.bar_connection',
            'priority' => 1000,
        ]);
        $this->assertContainerBuilderHasServiceDefinitionWithTag('runopencode.query_resources_loader.executor.doctrine.dbal.foo_connection', 'runopencode.query_resources_loader.executor', [
            'label'    => 'doctrine.dbal.foo_connection',
            'priority' => 0,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterDbalExecutors());
    }
}
