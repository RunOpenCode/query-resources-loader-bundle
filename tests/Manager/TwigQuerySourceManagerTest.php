<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Manager;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExecutionException;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutor;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutionResult;
use RunOpenCode\Bundle\QueryResourcesLoader\Manager\TwigQuerySourceManager;

class TwigQuerySourceManagerTest extends TestCase
{
    /**
     * @test
     */
    public function itHasQuery()
    {
        $this->assertTrue($this->getManager()->has('@test/query-1'));
    }

    /**
     * @test
     */
    public function itDoesNotHaveQuery()
    {
        $this->assertFalse($this->getManager()->has('unknown'));
    }

    /**
     * @test
     */
    public function itGetsQuery()
    {
        $this->assertSame('THIS IS SIMPLE, PLAIN QUERY', $this->getManager()->get('@test/query-1'));
        $this->assertSame('THIS IS SIMPLE, PLAIN QUERY WITH VARIABLE X', $this->getManager()->get('@test/query-2', array('var' => 'X')));
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Bundle\QueryResourcesLoader\Exception\SyntaxException
     */
    public function itThrowsSyntaxError()
    {
        $this->getManager()->get('@test/syntax-error');
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Bundle\QueryResourcesLoader\Exception\SourceNotFoundException
     */
    public function itThrowsNotFoundException()
    {
        $this->getManager()->get('not-existing');
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException
     */
    public function itThrowsUnknownException()
    {
        $twig = $this
            ->getMockBuilder(\Twig_Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twig
            ->method('render')
            ->willThrowException(new \Exception());

        $manager = new TwigQuerySourceManager($twig);

        $manager->get('does_not_exists');
    }

    /**
     * @test
     */
    public function itCanExecute()
    {
        $this->assertInstanceOf(DoctrineDbalExecutionResult::class, $this->getManager()->execute('@test/query-1'));
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException
     * @expectedExceptionMessage Requested executor "dummy" does not exists.
     */
    public function itThrowsExceptionWhenExecutorDoesNotExists()
    {
        $this->getManager()->execute('@test/query-1', array(), array(), 'dummy');
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExecutionException
     * @expectedExceptionMessage It throws library execution exception.
     */
    public function itThrowsLibraryExecutionException()
    {
        $manager = $this->getManager();

        $executor = $this
            ->getMockBuilder(ExecutorInterface::class)
            ->getMock();

        $executor
            ->method('execute')
            ->willThrowException(new ExecutionException('It throws library execution exception.'));

        $manager->registerExecutor($executor, 'throwing');

        $manager->execute('@test/query-1', array(), array(), 'throwing');
    }

    /**
     * @test
     *
     * @expectedException \RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExecutionException
     * @expectedExceptionMessage Query "@test/query-1" could not be executed.
     */
    public function itWrapsUnknownExceptionAndThrowsLibraryExecutionException()
    {
        $manager = $this->getManager();

        $executor = $this
            ->getMockBuilder(ExecutorInterface::class)
            ->getMock();

        $executor
            ->method('execute')
            ->willThrowException(new \Exception('It throws library execution exception.'));

        $manager->registerExecutor($executor, 'throwing');

        $manager->execute('@test/query-1', array(), array(), 'throwing');
    }

    private function getManager()
    {
        $manager = new TwigQuerySourceManager($this->getTwig());
        $manager->registerExecutor($this->getExecutor(), 'default');

        return $manager;
    }

    private function getTwig()
    {
        return new \Twig_Environment(new \Twig_Loader_Array(array(
            '@test/query-1' => 'THIS IS SIMPLE, PLAIN QUERY',
            '@test/query-2' => 'THIS IS SIMPLE, PLAIN QUERY WITH VARIABLE {{var}}',
            '@test/syntax-error' => 'THIS IS SIMPLE, PLAIN QUERY WITH TWIG SYNTAX ERROR {% if x',
        )));
    }

    private function getExecutor()
    {
        $stub = $this->createMock(\Doctrine\DBAL\Connection::class);

        $stub
            ->method('executeQuery')
            ->willReturn($this->createMock(\Doctrine\DBAL\Driver\Statement::class));

        return new DoctrineDbalExecutor($stub);
    }
}
