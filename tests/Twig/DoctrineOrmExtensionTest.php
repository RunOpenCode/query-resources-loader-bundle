<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Twig\Extension\DoctrineOrmExtension;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class DoctrineOrmExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function knownFunctions(): void
    {
        $registry = $this
            ->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extension = new DoctrineOrmExtension($registry);

        $functions = \array_map(static function (TwigFunction $function) {
            return $function->getName();
        }, $extension->getFunctions());

        $this->assertEquals([
            'table_name',
            'join_table_name',
            'column_name',
            'join_table_join_columns',
            'join_table_inverse_join_columns',
            'join_table_join_column',
            'join_table_inverse_join_column',
            'primary_key_column_name',
        ], $functions);
    }

    /**
     * @test
     */
    public function knownFilters(): void
    {
        $registry = $this
            ->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extension = new DoctrineOrmExtension($registry);

        $filters = \array_map(function (TwigFilter $filter) {
            return $filter->getName();
        }, $extension->getFilters());

        $this->assertEquals([
            'table_name',
            'join_table_name',
            'column_name',
            'join_table_join_columns',
            'join_table_inverse_join_columns',
            'join_table_join_column',
            'join_table_inverse_join_column',
            'primary_key_column_name',
        ], $filters);
    }

    /**
     * @test
     */
    public function itGetsTableName(): void
    {
        $registry = $this
            ->getMockBuilder(ManagerRegistry::class)
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
         * @var TwigFunction $function
         */
        $function = \array_values(\array_filter($extension->getFunctions(), function (TwigFunction $function) {
            return 'table_name' === $function->getName();
        }))[0];

        $this->assertEquals('some_table_name', \call_user_func($function->getCallable(), ''));
    }

    /**
     * @test
     */
    public function itGetsColumnName(): void
    {
        $registry = $this
            ->getMockBuilder(ManagerRegistry::class)
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
         * @var TwigFunction $function
         */
        $function = \array_values(\array_filter($extension->getFunctions(), static function (TwigFunction $function) {
            return 'column_name' === $function->getName();
        }))[0];

        $this->assertEquals('some_column_name', \call_user_func($function->getCallable(), '', ''));
    }

    /**
     * @test
     */
    public function itGetsJoinTableName(): void
    {
        $this->markTestIncomplete('Missing implementation');
    }

    /**
     * @test
     */
    public function itGetsJoinTableJoinColumns(): void
    {
        $this->markTestIncomplete('Missing implementation');
    }

    /**
     * @test
     */
    public function itGetsJoinTableInverseJoinColumns(): void
    {
        $this->markTestIncomplete('Missing implementation');
    }

    /**
     * @test
     */
    public function itGetsJoinTableJoinColumn(): void
    {
        $this->markTestIncomplete('Missing implementation');
    }

    /**
     * @test
     */
    public function itGetsJoinTableInverseJoinColumn(): void
    {
        $this->markTestIncomplete('Missing implementation');
    }

    /**
     * @test
     */
    public function itGetsPrimaryKeyColumnName(): void
    {
        $this->markTestIncomplete('Missing implementation');
    }
}
