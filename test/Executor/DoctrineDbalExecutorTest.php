<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Executor;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutor;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutorResult;

class DoctrineDbalExecutorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DoctrineDbalExecutor
     */
    protected $executor;

    public function setUp()
    {
        $connection = DriverManager::getConnection(array(
            'memory' => true,
            'driver' => 'pdo_sqlite'
        ));

        $schema = new Schema();

        $myTable = $schema->createTable('test');
        $myTable->addColumn('id', 'integer', array('unsigned' => true));
        $myTable->addColumn('title', 'string', array('length' => 32));
        $myTable->addColumn('description', 'string', array('length' => 255));
        $myTable->setPrimaryKey(array('id'));

        $connection->executeQuery($schema->toSql($connection->getDatabasePlatform())[0]);

        $records = [
            ['id' => 1, 'title' => 'Some title 1', 'description' => 'Some description 1'],
            ['id' => 2, 'title' => 'Some title 2', 'description' => 'Some description 2'],
            ['id' => 3, 'title' => 'Some title 3', 'description' => 'Some description 3'],
            ['id' => 4, 'title' => 'Some title 4', 'description' => 'Some description 4'],
            ['id' => 5, 'title' => 'Some title 5', 'description' => 'Some description 5']
        ];

        foreach ($records as $record) {
            $connection->executeQuery('INSERT INTO test (id, title, description) VALUES (:id, :title, :description);', $record);
        }

        $this->executor = new DoctrineDbalExecutor($connection);
    }

    /**
     * @test
     */
    public function itExecutesQueries()
    {
        $result = $this->executor->execute('SELECT * FROM test', array());

        $this->assertInstanceOf(DoctrineDbalExecutorResult::class, $result);
    }

    /**
     * @test
     */
    public function itGivesSingleScalarResult()
    {
        $result = $this->executor->execute('SELECT COUNT(*) as cnt FROM test;', array());

        $this->assertEquals(5, $result->getSingleScalarResult());
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Bundle\QueryResourcesLoader\Exception\NoResultException
     */
    public function itDoesNotHaveSingleScalarResult()
    {
        $result = $this->executor->execute('SELECT * FROM test WHERE 1 = 0;', array());

        $result->getSingleScalarResult();
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Bundle\QueryResourcesLoader\Exception\NonUniqueResultException
     */
    public function itHaveMoreThanSingleScalarResult()
    {
        $result = $this->executor->execute('SELECT * FROM test;', array());

        $result->getSingleScalarResult();
    }

    /**
     * @test
     */
    public function itGivesScalarResult()
    {
        $result = $this->executor->execute('SELECT id FROM test ORDER BY id ASC;', array());

        $this->assertEquals([1, 2, 3, 4, 5], $result->getScalarResult());
    }


    /**
     * @test
     */
    public function itGivesSingleRowResult()
    {
        $result = $this->executor->execute('SELECT id, title, description FROM test WHERE id = 3;', array());

        $this->assertEquals([
            'id' => 3, 'title' => 'Some title 3', 'description' => 'Some description 3',
            0 => 3, 1 => 'Some title 3', 2 => 'Some description 3'
        ], $result->getSingleRowResult());
    }
}

