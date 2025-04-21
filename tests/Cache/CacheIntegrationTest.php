<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Cache;

use Doctrine\DBAL\Driver\Result;
use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheIdentity;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\KernelTestCase;

final class CacheIntegrationTest extends KernelTestCase
{
    public function testCacheIntegration(): void
    {
        $this->createFixtures();

        /** @var QueryResourcesLoaderInterface $loader */
        $loader = $this->getContainer()->get(QueryResourcesLoaderInterface::class);
        /** @var Result $resultSet */
        $resultSet = $loader->execute(
            'SELECT id, title FROM bar ORDER BY id',
            null,
            Options::create([
                'cache'  => new CacheIdentity(
                    'foo',
                ),
                'loader' => 'raw',
            ]),
        );

        $this->assertEquals([
            ['id' => 1, 'title' => 'Bar title 1'],
            ['id' => 2, 'title' => 'Bar title 2'],
            ['id' => 3, 'title' => 'Bar title 3'],
            ['id' => 4, 'title' => 'Bar title 4'],
            ['id' => 5, 'title' => 'Bar title 5'],
        ], $resultSet->fetchAllAssociative());

        $this->ensureKernelShutdown();
        $this->bootKernel();

        /** @var QueryResourcesLoaderInterface $loader */
        $loader = $this->getContainer()->get(QueryResourcesLoaderInterface::class);
        /** @var Result $resultSet */
        $resultSet = $loader->execute(
            'SELECT * FROM bar',
            null,
            Options::create([
                'cache'  => new CacheIdentity(
                    'foo',
                ),
                'loader' => 'raw',
            ]),
        );

        $this->assertEquals([
            ['id' => 1, 'title' => 'Bar title 1'],
            ['id' => 2, 'title' => 'Bar title 2'],
            ['id' => 3, 'title' => 'Bar title 3'],
            ['id' => 4, 'title' => 'Bar title 4'],
            ['id' => 5, 'title' => 'Bar title 5'],
        ], $resultSet->fetchAllAssociative());
    }
}
