<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Resources\App;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use RunOpenCode\Bundle\QueryResourcesLoader\QueryResourcesLoaderBundle;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Fixtures\BarFixtures;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Fixtures\Fixtures;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Fixtures\FooFixtures;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new QueryResourcesLoaderBundle(),
            new MonologBundle(),
        ];
    }

    public function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'test'    => true,
            'secret'  => 'foo',
            'session' => [
                'storage_factory_id' => 'session.storage.factory.mock_file',
            ],
            'cache'   => [
                'pools' => [
                    'app.roc_test_cache' => [
                        'adapter' => 'cache.adapter.filesystem',
                        'tags'    => true,
                    ],
                ],
            ],
        ]);

        $container->extension('doctrine', [
            'dbal' => [
                'default_connection' => 'bar',
                'connections'        => [
                    'foo' => [
                        'driver'         => 'pdo_sqlite',
                        'memory'         => true,
                        'use_savepoints' => true,
                        'logging'        => true,
                    ],
                    'bar' => [
                        'driver'         => 'pdo_sqlite',
                        'memory'         => true,
                        'use_savepoints' => true,
                        'logging'        => true,
                    ],
                ],
            ],
        ]);

        $container->extension('monolog', [
            'handlers' => [
                'main' => [
                    'type'  => 'test',
                    'level' => 'debug',
                ],
            ],
        ]);

        $container->extension('runopencode_query_resources_loader', [
            'cache' => [
                'pool' => 'app.roc_test_cache',
            ],
        ]);

        $container
            ->services()
            ->set(FooFixtures::class, FooFixtures::class)
            ->arg(0, service('doctrine.dbal.foo_connection'));

        $container
            ->services()
            ->set(BarFixtures::class, BarFixtures::class)
            ->arg(0, service('doctrine.dbal.bar_connection'));

        $container
            ->services()
            ->set(Fixtures::class, Fixtures::class)
            ->autowire()
            ->public();
    }
}
