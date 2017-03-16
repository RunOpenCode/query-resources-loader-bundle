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
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutor;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutorResult;
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
        $this->assertInstanceOf(DoctrineDbalExecutorResult::class, $this->getManager()->execute('@test/query-1'));
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
