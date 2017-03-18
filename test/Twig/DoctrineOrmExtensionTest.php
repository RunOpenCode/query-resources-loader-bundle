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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Twig\DoctrineOrmExtension;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DoctrineOrmExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function itHasName()
    {
        $registry = $this
            ->getMockBuilder(RegistryInterface::class)
            ->getMock();

        $extension = new DoctrineOrmExtension($registry);

        $this->assertEquals('run_open_code_query_resources_loader', $extension->getName());
    }

    /**
     * @test
     */
    public function knownFunctions()
    {
        $registry = $this
            ->getMockBuilder(RegistryInterface::class)
            ->getMock();

        $extension = new DoctrineOrmExtension($registry);

        $functions = array_map(function(\Twig_Function $function) {
            return $function->getName();
        }, $extension->getFunctions());

        $this->assertEquals(array('table_name', 'column_name'), $functions);
    }

    /**
     * @test
     */
    public function knownFilters()
    {
        $registry = $this
            ->getMockBuilder(RegistryInterface::class)
            ->getMock();

        $extension = new DoctrineOrmExtension($registry);

        $filters = array_map(function(\Twig_Filter $filter) {
            return $filter->getName();
        }, $extension->getFilters());

        $this->assertEquals(array('table_name', 'column_name'), $filters);
    }

    /**
     * @test
     */
    public function itGetsTableName()
    {
        $registry = $this
            ->getMockBuilder(RegistryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metadata = $this
            ->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metadata
            ->method('getTableName')
            ->willReturn('some_table_name');

        $manager
            ->method('getClassMetadata')
            ->willReturn($metadata);

        $registry
            ->method('getManagerForClass')
            ->willReturn($manager);

        $extension = new DoctrineOrmExtension($registry);

        /**
         * @var \Twig_Function $function
         */
        $function = array_values(array_filter($extension->getFunctions(), function(\Twig_Function $function) {
            return 'table_name' === $function->getName();
        }))[0];

        $this->assertEquals('some_table_name', call_user_func($function->getCallable(), ''));
    }

    /**
     * @test
     */
    public function itGetsColumnName()
    {
        $registry = $this
            ->getMockBuilder(RegistryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metadata = $this
            ->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $metadata
            ->method('getColumnName')
            ->willReturn('some_column_name');

        $manager
            ->method('getClassMetadata')
            ->willReturn($metadata);

        $registry
            ->method('getManagerForClass')
            ->willReturn($manager);

        $extension = new DoctrineOrmExtension($registry);

        /**
         * @var \Twig_Function $function
         */
        $function = array_values(array_filter($extension->getFunctions(), function(\Twig_Function $function) {
            return 'column_name' === $function->getName();
        }))[0];

        $this->assertEquals('some_column_name', call_user_func($function->getCallable(), '', ''));
    }
}
