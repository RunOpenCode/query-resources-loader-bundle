<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Executor\Dbal;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\TransactionIsolationLevel;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NonUniqueResultException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NoResultException;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalOptions;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DoctrineDbalExecutionResult;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DoctrineDbalQueryExecutor;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;
use Symfony\Component\ErrorHandler\BufferingLogger;

final class DoctrineDbalExecutorTest extends TestCase
{
    /**
     * @var DoctrineDbalQueryExecutor
     */
    private DoctrineDbalQueryExecutor $executor;

    private Connection $connection;

    private BufferingLogger $logger;

    public function setUp(): void
    {
        $configuration = new Configuration();
        $this->logger  = new BufferingLogger();

        $configuration->setMiddlewares([new Middleware($this->logger)]);

        $this->connection = DriverManager::getConnection([
            'memory' => true,
            'driver' => 'pdo_sqlite',
        ], $configuration);

        $schema = new Schema();

        $myTable = $schema->createTable('test');
        $myTable->addColumn('id', 'integer', ['unsigned' => true]);
        $myTable->addColumn('title', 'string', ['length' => 32]);
        $myTable->addColumn('description', 'string', ['length' => 255]);
        $myTable->setPrimaryKey(['id']);

        $this->connection->executeQuery($schema->toSql($this->connection->getDatabasePlatform())[0]);

        $records = [
            ['id' => 1, 'title' => 'Some title 1', 'description' => 'Some description 1'],
            ['id' => 2, 'title' => 'Some title 2', 'description' => 'Some description 2'],
            ['id' => 3, 'title' => 'Some title 3', 'description' => 'Some description 3'],
            ['id' => 4, 'title' => 'Some title 4', 'description' => 'Some description 4'],
            ['id' => 5, 'title' => 'Some title 5', 'description' => 'Some description 5'],
        ];

        foreach ($records as $record) {
            $this->connection->executeQuery('INSERT INTO test (id, title, description) VALUES (:id, :title, :description);', $record);
        }

        $this->executor = new DoctrineDbalQueryExecutor($this->connection, 'default');

        $this->logger->cleanLogs();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->logger->cleanLogs();
    }

    public function testItExecutesQueries(): void
    {
        $result = $this->executor->execute('SELECT * FROM test', Parameters::create(), Options::create());

        $this->assertInstanceOf(DoctrineDbalExecutionResult::class, $result);
    }

    public function testItGivesSingleScalarResult(): void
    {
        $result = $this->executor->execute('SELECT COUNT(*) as cnt FROM test', Parameters::create(), Options::create());

        $this->assertEquals(5, $result->getSingleScalarResult());
    }

    public function testItDoesNotHaveSingleScalarResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0', Parameters::create(), Options::create());

        $this->expectException(NoResultException::class);

        $result->getSingleScalarResult();
    }

    public function testItHaveMoreThanSingleScalarResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test', Parameters::create(), Options::create());

        $this->expectException(NonUniqueResultException::class);

        $result->getSingleScalarResult();
    }

    public function testItGivesDefaultWhereThereIsNoSingleScalarResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0', Parameters::create(), Options::create());

        $this->assertTrue($result->getSingleScalarResultOrDefault(true));
    }

    public function testItGivesNullWhereThereIsNoSingleScalarResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0', Parameters::create(), Options::create());

        $this->assertNull($result->getSingleScalarResultOrNull());
    }

    public function testItGivesScalarResult(): void
    {
        $result = $this->executor->execute('SELECT id FROM test ORDER BY id ASC', Parameters::create(), Options::create());

        $this->assertEquals([1, 2, 3, 4, 5], $result->getScalarResult());
    }

    public function testItGivesDefaultWhereThereIsNoScalarResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0', Parameters::create(), Options::create());

        $this->assertTrue($result->getScalarResultOrDefault(true));
    }

    public function testItGivesNullWhereThereIsNoScalarResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0', Parameters::create(), Options::create());

        $this->assertNull($result->getScalarResultOrNull());
    }

    public function testItGivesSingleRowResult(): void
    {
        $result = $this->executor->execute('SELECT id, title, description FROM test WHERE id = 3', Parameters::create(), Options::create());

        $this->assertSame([
            'id'          => 3,
            'title'       => 'Some title 3',
            'description' => 'Some description 3',
        ], $result->getSingleResult());
    }

    public function testItHaveMoreThanSingleRowResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test', Parameters::create(), Options::create());

        $this->expectException(NonUniqueResultException::class);

        $result->getSingleResult();
    }

    public function testItDoesNotHaveSingleRowResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0', Parameters::create(), Options::create());

        $this->expectException(NoResultException::class);

        $result->getSingleResult();
    }

    public function testItGivesDefaultWhereThereIsNoSingeRowResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0', Parameters::create(), Options::create());

        $this->assertTrue($result->getSingleResultOrDefault(true));
    }

    public function testItGivesNullWhereThereIsNoSingeRowResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0', Parameters::create(), Options::create());

        $this->assertNull($result->getSingleResultOrNull());
    }

    public function testItProvidesTransactionalSupport(): void
    {
        $isolation = $this->connection->getTransactionIsolation();

        $this->assertNotSame($isolation, TransactionIsolationLevel::READ_UNCOMMITTED);

        $this->executor->execute('SELECT id FROM test', Parameters::create(), Options::create())->getScalarResult();

        $this->assertSame('SELECT id FROM test', $this->logger->cleanLogs()[0][2]['sql']);

        $this->executor->beginTransaction(DbalOptions::create(['isolation' => TransactionIsolationLevel::READ_UNCOMMITTED]));

        try {
            $this->executor->execute('SELECT id FROM test', Parameters::create(), Options::create());
            $this->executor->execute('SELECT title FROM test', Parameters::create(), Options::create());

            $this->executor->commit();
        } catch (\Exception) {
            $this->executor->rollback();
        }

        $logs = $this->logger->cleanLogs();

        $this->assertStringContainsString('PRAGMA read_uncommitted = 0', $logs[0][2]['sql']);
        $this->assertStringContainsString('Beginning transaction', $logs[1][1]);
        $this->assertStringContainsString('SELECT id FROM test', $logs[2][2]['sql']);
        $this->assertStringContainsString('SELECT title FROM test', $logs[3][2]['sql']);
        $this->assertStringContainsString('Committing transaction', $logs[4][1]);
        $this->assertStringContainsString('PRAGMA read_uncommitted = 1', $logs[5][2]['sql']);

        $this->assertCount(6, $logs);

        $this->assertSame($this->connection->getTransactionIsolation(), $isolation);
    }

    public function testItExecutesQueryWithNonDefaultIsolationLevel(): void
    {
        $isolation = $this->connection->getTransactionIsolation();

        $this->assertNotSame($isolation, TransactionIsolationLevel::READ_UNCOMMITTED);

        $this->executor->execute('SELECT id FROM test', Parameters::create(), DbalOptions::create(['isolation' => TransactionIsolationLevel::READ_UNCOMMITTED]));

        $logs = $this->logger->cleanLogs();

        $this->assertStringContainsString('PRAGMA read_uncommitted = 0', $logs[0][2]['sql']);
        $this->assertStringContainsString('Beginning transaction', $logs[1][1]);
        $this->assertStringContainsString('SELECT id FROM test', $logs[2][2]['sql']);
        $this->assertStringContainsString('Committing transaction', $logs[3][1]);
        $this->assertStringContainsString('PRAGMA read_uncommitted = 1', $logs[4][2]['sql']);
    }

    public function testItCountsResults(): void
    {
        $result = $this->executor->execute('SELECT * FROM test', Parameters::create(), Options::create());

        $this->assertCount(5, $result);
    }
}
