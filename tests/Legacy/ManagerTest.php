<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Legacy;

use Doctrine\DBAL\TransactionIsolationLevel;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DoctrineDbalExecutionResult;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\KernelTestCase;

final class ManagerTest extends KernelTestCase
{
    private ManagerInterface $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->getContainer()->get(ManagerInterface::class); // @phpstan-ignore-line

        $this->createFixtures();
    }

    public function testItExecutesFromDefaultExecutor(): void
    {
        $result = $this->manager->execute('get_all_from_default.sql.twig');

        $this->assertInstanceOf(DoctrineDbalExecutionResult::class, $result);
        $this->assertSame([
            'id'          => 1,
            'title'       => 'Bar title 1',
            'description' => 'Bar description 1',
        ], [...$result][0] ?? null);
    }

    public function testItExecutesFromSelectedExecutor(): void
    {
        $result = $this->manager->execute('get_all_from_foo.sql.twig', [], [], 'doctrine.dbal.foo_connection');

        $this->assertInstanceOf(DoctrineDbalExecutionResult::class, $result);
        $this->assertSame([
            'id'          => 1,
            'title'       => 'Foo title 1',
            'description' => 'Foo description 1',
        ], [...$result][0] ?? null);
    }

    public function testTransactionalExecutionWithDefaultExecutor(): void
    {
        $result = $this->manager->transactional(function(ExecutorInterface $executor): ExecutionResultInterface {
            return $executor->execute('get_all_from_default.sql.twig');
        });

        $this->assertInstanceOf(DoctrineDbalExecutionResult::class, $result);
        $this->assertSame([
            'id'          => 1,
            'title'       => 'Bar title 1',
            'description' => 'Bar description 1',
        ], [...$result][0] ?? null);
    }

    public function testTransactionalExecutionWithSelectedExecutor(): void
    {
        $result = $this->manager->transactional(function(ExecutorInterface $executor): ExecutionResultInterface {
            return $executor->execute('get_all_from_foo.sql.twig');
        }, [
            'isolation' => TransactionIsolationLevel::READ_UNCOMMITTED,
        ], 'doctrine.dbal.foo_connection');

        $this->assertInstanceOf(DoctrineDbalExecutionResult::class, $result);
        $this->assertSame([
            'id'          => 1,
            'title'       => 'Foo title 1',
            'description' => 'Foo description 1',
        ], [...$result][0] ?? null);
    }

    public function testItThrowsExceptionWhenExecutorDoesNotExists(): void
    {
        $this->expectException(RuntimeException::class);

        $this->manager->execute('get_all_from_default.sql.twig', [], [], 'qux');
    }
}
