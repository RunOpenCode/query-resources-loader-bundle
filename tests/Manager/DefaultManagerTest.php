<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Manager;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\LoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExecutionException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutor;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutionResult;
use RunOpenCode\Bundle\QueryResourcesLoader\Manager\DefaultManager;

final class DefaultManagerTest extends TestCase
{
    /**
     * @test
     */
    public function itCanExecute(): void
    {
        $this->assertInstanceOf(DoctrineDbalExecutionResult::class, $this->getManager()->execute('@test/query-1'));
    }

    /**
     * @test
     */
    public function itThrowsExceptionWhenExecutorDoesNotExists(): void
    {
        $this->expectException(RuntimeException::class);
        $this->getManager()->execute('@test/query-1', [], [], 'dummy');
    }

    /**
     * @test
     */
    public function itThrowsLibraryExecutionException(): void
    {
        /** @var DefaultManager $manager */
        $manager = $this->getManager();

        $executor = $this
            ->getMockBuilder(ExecutorInterface::class)
            ->getMock();

        $executor
            ->method('execute')
            ->willThrowException(new ExecutionException('It throws library execution exception.'));

        $manager->registerExecutor($executor, 'throwing');

        $this->expectException(ExecutionException::class);

        $manager->execute('@test/query-1', [], [], 'throwing');
    }

    /**
     * @test
     */
    public function itWrapsUnknownExceptionAndThrowsLibraryExecutionException(): void
    {
        /** @var DefaultManager $manager */
        $manager = $this->getManager();

        $executor = $this
            ->getMockBuilder(ExecutorInterface::class)
            ->getMock();

        $executor
            ->method('execute')
            ->willThrowException(new \Exception('It throws library execution exception.'));

        $manager->registerExecutor($executor, 'throwing');

        $this->expectException(ExecutionException::class);

        $manager->execute('@test/query-1', [], [], 'throwing');
    }

    private function getManager(): ManagerInterface
    {
        $manager = new DefaultManager();
        $manager->registerExecutor($this->getExecutor(), 'default');

        return $manager;
    }

    private function getExecutor(): ExecutorInterface
    {
        $connection = $this->createMock(Connection::class);
        $loader     = $this->createMock(LoaderInterface::class);

        $connection
            ->method('executeQuery')
            ->willReturn($this->createMock(Result::class));

        return new DoctrineDbalExecutor($connection, $loader);
    }
}
