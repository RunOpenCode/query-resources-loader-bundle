<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalOptions;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;

final class QueryResourcesLoaderTest extends KernelTestCase
{
    private QueryResourcesLoaderInterface $loader;

    public function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->getContainer()->get(QueryResourcesLoaderInterface::class); // @phpstan-ignore-line

        $this->createFixtures();
    }

    public function testItExecutesQuery(): void
    {
        $count = $this->loader->execute('bar/count_from_bar.sql.twig')->getSingleScalarResult();

        $this->assertSame(5, $count);
        $this->assertSame([
            'SELECT COUNT(*) AS cnt FROM bar',
        ], $this->getLoggedQueryStatements()['bar']);
    }

    public function testItExecutesTransactionalQuery(): void
    {
        $count = $this->loader->execute('bar/count_from_bar.sql.twig', Parameters::create(), DbalOptions::readUncommitted())->getSingleScalarResult();

        $this->assertSame(5, $count);
        $this->assertSame([
            'PRAGMA read_uncommitted = 0',
            '"START TRANSACTION"',
            'SELECT COUNT(*) AS cnt FROM bar',
            '"COMMIT"',
            'PRAGMA read_uncommitted = 1',
        ], $this->getLoggedQueryStatements()['bar']);
    }

    public function testItExecutesTransactionUsingDefaultExecutor(): void
    {
        [$count, $row] = $this->loader->transactional(function(QueryResourcesLoaderInterface $loader): array {
            return [
                $loader->execute('bar/count_from_bar.sql.twig')->getSingleScalarResult(),
                [...$loader->execute('get_all_from_default.sql.twig')][0],
            ];
        });

        $this->assertSame(5, $count);
        $this->assertSame([
            'id'          => 1,
            'title'       => 'Bar title 1',
            'description' => 'Bar description 1',
        ], $row);

        $this->assertSame([
            '"START TRANSACTION"',
            'SELECT COUNT(*) AS cnt FROM bar',
            'SELECT * FROM bar;',
            '"COMMIT"',
        ], $this->getLoggedQueryStatements()['bar']);
    }

    public function testItRollbacksTransaction(): void
    {
        try {
            $this->loader->transactional(function(QueryResourcesLoaderInterface $loader): int {
                $loader->execute('bar/count_from_bar.sql.twig')->getSingleScalarResult();
                throw new \Exception();
            });

            /** @psalm-suppress UnevaluatedCode */
            $this->fail('Expect exception to be thrown.'); // @phpstan-ignore-line
        } catch (\Exception) {
            // noop
        }

        $this->assertSame([
            '"START TRANSACTION"',
            'SELECT COUNT(*) AS cnt FROM bar',
            '"ROLLBACK"',
        ], $this->getLoggedQueryStatements()['bar']);
    }

    public function testItExecutesDistributedTransaction(): void
    {
        [$foo, $bar] = $this->loader->transactional(function(QueryResourcesLoaderInterface $loader): array {
            return [
                [...$loader->execute('get_all_from_foo.sql.twig', null, Options::executor('doctrine.dbal.foo_connection'))][0],
                [...$loader->execute('get_all_from_default.sql.twig', null, Options::executor('doctrine.dbal.bar_connection'))][0],
            ];
        }, Options::executor('doctrine.dbal.foo_connection'), Options::executor('doctrine.dbal.bar_connection'));

        $this->assertSame([
            'id'          => 1,
            'title'       => 'Foo title 1',
            'description' => 'Foo description 1',
        ], $foo);

        $this->assertSame([
            'id'          => 1,
            'title'       => 'Bar title 1',
            'description' => 'Bar description 1',
        ], $bar);

        $this->assertSame([
            '"START TRANSACTION"',
            'SELECT * FROM foo;',
            '"COMMIT"',
        ], $this->getLoggedQueryStatements()['foo']);

        $this->assertSame([
            '"START TRANSACTION"',
            'SELECT * FROM bar;',
            '"COMMIT"',
        ], $this->getLoggedQueryStatements()['bar']);
    }

    public function testItRolesBackDistributedTransaction(): void
    {
        try {
            $this->loader->transactional(function(QueryResourcesLoaderInterface $loader): array {
                $loader->execute('get_all_from_foo.sql.twig', null, Options::executor('doctrine.dbal.foo_connection'));
                $loader->execute('get_all_from_default.sql.twig', null, Options::executor('doctrine.dbal.bar_connection'));

                throw new \Exception();

            }, Options::executor('doctrine.dbal.foo_connection'), Options::executor('doctrine.dbal.bar_connection'));

            /** @psalm-suppress UnevaluatedCode */
            $this->fail('Expect exception to be thrown.'); // @phpstan-ignore-line
        } catch (\Exception) {
            // noop
        }

        $this->assertSame([
            '"START TRANSACTION"',
            'SELECT * FROM foo;',
            '"ROLLBACK"',
        ], $this->getLoggedQueryStatements()['foo']);

        $this->assertSame([
            '"START TRANSACTION"',
            'SELECT * FROM bar;',
            '"ROLLBACK"',
        ], $this->getLoggedQueryStatements()['bar']);
    }
}
