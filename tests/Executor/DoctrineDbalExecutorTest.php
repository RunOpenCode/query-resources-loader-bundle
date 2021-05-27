<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Executor;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\EchoSQLLogger;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\TransactionIsolationLevel;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NonUniqueResultException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NoResultException;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutor;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutionResult;

final class DoctrineDbalExecutorTest extends TestCase
{
    /**
     * @var DoctrineDbalExecutor
     */
    private DoctrineDbalExecutor $executor;

    private Connection $connection;

    public function setUp(): void
    {
        $this->connection = DriverManager::getConnection([
            'memory' => true,
            'driver' => 'pdo_sqlite',
        ]);

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

        $this->executor = new DoctrineDbalExecutor($this->connection);
    }

    /**
     * @test
     */
    public function itExecutesQueries(): void
    {
        $result = $this->executor->execute('SELECT * FROM test', []);

        $this->assertInstanceOf(DoctrineDbalExecutionResult::class, $result);
    }

    /**
     * @test
     */
    public function itGivesSingleScalarResult(): void
    {
        $result = $this->executor->execute('SELECT COUNT(*) as cnt FROM test;', []);

        $this->assertEquals(5, $result->getSingleScalarResult());
    }

    /**
     * @test
     */
    public function itDoesNotHaveSingleScalarResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0;', []);

        $this->expectException(NoResultException::class);

        $result->getSingleScalarResult();
    }

    /**
     * @test
     */
    public function itHaveMoreThanSingleScalarResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test;', []);

        $this->expectException(NonUniqueResultException::class);

        $result->getSingleScalarResult();
    }

    /**
     * @test
     */
    public function itGivesDefaultWhereThereIsNoSingleScalarResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0;', []);

        $this->assertTrue($result->getSingleScalarResultOrDefault(true));
    }


    /**
     * @test
     */
    public function itGivesNullWhereThereIsNoSingleScalarResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0;', []);

        $this->assertNull($result->getSingleScalarResultOrNull());
    }

    /**
     * @test
     */
    public function itGivesScalarResult(): void
    {
        $result = $this->executor->execute('SELECT id FROM test ORDER BY id ASC;', []);

        $this->assertEquals([1, 2, 3, 4, 5], $result->getScalarResult());
    }

    /**
     * @test
     */
    public function itGivesDefaultWhereThereIsNoScalarResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0;', []);

        $this->assertTrue($result->getScalarResultOrDefault(true));
    }

    /**
     * @test
     */
    public function itGivesNullWhereThereIsNoScalarResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0;', []);

        $this->assertNull($result->getScalarResultOrNull());
    }

    /**
     * @test
     */
    public function itGivesSingleRowResult(): void
    {
        $result = $this->executor->execute('SELECT id, title, description FROM test WHERE id = 3;', []);

        $this->assertEquals([
            'id' => 3, 'title' => 'Some title 3', 'description' => 'Some description 3',
            0    => 3, 1 => 'Some title 3', 2 => 'Some description 3',
        ], $result->getSingleResult());
    }

    /**
     * @test
     */
    public function itHaveMoreThanSingleRowResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test;', []);

        $this->expectException(NonUniqueResultException::class);

        $result->getSingleResult();
    }

    /**
     * @test
     */
    public function itDoesNotHaveSingleRowResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0;', []);

        $this->expectException(NoResultException::class);

        $result->getSingleResult();
    }

    /**
     * @test
     */
    public function itGivesDefaultWhereThereIsNoSingeRowResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0;', []);

        $this->assertTrue($result->getSingleResultOrDefault(true));
    }

    /**
     * @test
     */
    public function itGivesNullWhereThereIsNoSingeRowResult(): void
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0;', []);

        $this->assertNull($result->getSingleResultOrNull());
    }

    /**
     * @test
     */
    public function itSetsIsolationLevel(): void
    {
        $this->connection->getConfiguration()->setSQLLogger(new EchoSQLLogger());
        $isolation = $this->connection->getTransactionIsolation();

        $this->assertNotSame($isolation, TransactionIsolationLevel::READ_UNCOMMITTED);

        \ob_start();
        $this->executor->execute('SELECT * FROM test WHERE 1 = 0;');
        $this->assertSame('SELECT * FROM test WHERE 1 = 0;', \trim(\ob_get_clean()));

        \ob_start();
        $this->executor->execute('SELECT * FROM test WHERE 1 = 0;', [], [], ['isolation' => TransactionIsolationLevel::READ_UNCOMMITTED]);
        $logged = \ob_get_clean();
        $this->assertStringContainsString('PRAGMA read_uncommitted = 1', $logged);
        $this->assertStringContainsString('SELECT * FROM test WHERE 1 = 0;', $logged);

        $this->assertSame($this->connection->getTransactionIsolation(), $isolation);
    }

    /**
     * @test
     */
    public function itCountsResults(): void
    {
        $result = $this->executor->execute('SELECT * FROM test;');
        \Closure::bind(function () {
            $this->debug = false;
        }, $result, DoctrineDbalExecutionResult::class)();
        $this->assertSame(5, \count($result));
    }

    /**
     * @test
     */
    public function itWarnsWhenUsingCountable(): void
    {
        $this->expectNotice();
        $result = $this->executor->execute('SELECT * FROM test;');
        \count($result);
    }
}
