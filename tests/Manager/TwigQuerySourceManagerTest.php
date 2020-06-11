<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Manager;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExecutionException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SourceNotFoundException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SyntaxException;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutor;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutionResult;
use RunOpenCode\Bundle\QueryResourcesLoader\Manager\TwigQuerySourceManager;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class TwigQuerySourceManagerTest extends TestCase
{
    /**
     * @test
     */
    public function itHasQuery(): void
    {
        $this->assertTrue($this->getManager()->has('@test/query-1'));
    }

    /**
     * @test
     */
    public function itDoesNotHaveQuery(): void
    {
        $this->assertFalse($this->getManager()->has('unknown'));
    }

    /**
     * @test
     */
    public function itGetsQuery(): void
    {
        $this->assertSame('THIS IS SIMPLE, PLAIN QUERY', $this->getManager()->get('@test/query-1'));
        $this->assertSame('THIS IS SIMPLE, PLAIN QUERY WITH VARIABLE X', $this->getManager()->get('@test/query-2', ['var' => 'X']));
    }

    /**
     * @test
     */
    public function itThrowsSyntaxError(): void
    {
        $this->expectException(SyntaxException::class);
        $this->getManager()->get('@test/syntax-error');
    }

    /**
     * @test
     */
    public function itThrowsNotFoundException(): void
    {
        $this->expectException(SourceNotFoundException::class);
        $this->getManager()->get('not-existing');
    }

    /**
     * @test
     */
    public function itThrowsUnknownException(): void
    {
        $twig = $this
            ->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twig
            ->method('render')
            ->willThrowException(new \Exception());

        $manager = new TwigQuerySourceManager($twig);

        $this->expectException(RuntimeException::class);

        $manager->get('does_not_exists');
    }

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
        $manager = new TwigQuerySourceManager($this->getTwig());
        $manager->registerExecutor($this->getExecutor(), 'default');

        return $manager;
    }

    private function getTwig(): Environment
    {
        return new Environment(new ArrayLoader([
            '@test/query-1'      => 'THIS IS SIMPLE, PLAIN QUERY',
            '@test/query-2'      => 'THIS IS SIMPLE, PLAIN QUERY WITH VARIABLE {{var}}',
            '@test/syntax-error' => 'THIS IS SIMPLE, PLAIN QUERY WITH TWIG SYNTAX ERROR {% if x',
        ]));
    }

    private function getExecutor(): ExecutorInterface
    {
        $stub = $this->createMock(Connection::class);

        $stub
            ->method('executeQuery')
            ->willReturn($this->createMock(Statement::class));

        return new DoctrineDbalExecutor($stub);
    }
}
