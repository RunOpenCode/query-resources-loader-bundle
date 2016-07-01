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

use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutor;

class DoctrineDbalExecutorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function itExecutesQueries()
    {
        $executor = new DoctrineDbalExecutor($this->getConnection());

        $result = $executor->execute('DUMMY SQL', array());

        $this->assertInstanceOf(\Doctrine\DBAL\Driver\Statement::class, $result);
    }

    private function getConnection()
    {
        $stub = $this->createMock(\Doctrine\DBAL\Connection::class);

        $stub
            ->method('executeQuery')
            ->willReturn($this->createMock(\Doctrine\DBAL\Driver\Statement::class));

        return $stub;
    }
}

