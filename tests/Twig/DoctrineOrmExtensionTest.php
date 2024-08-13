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
    public function testKnownFunctions(): void
    {
        $registry = $this
            ->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extension = new DoctrineOrmExtension($registry);

        $functions = \array_map(static function(TwigFunction $function) {
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

    public function testKnownFilters(): void
    {
        $registry = $this
            ->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extension = new DoctrineOrmExtension($registry);

        $filters = \array_map(function(TwigFilter $filter) {
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

    public function testItGetsTableName(): void
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
        $function  = \array_values(\array_filter($extension->getFunctions(), function(TwigFunction $function) {
            return 'table_name' === $function->getName();
        }))[0];

        /**
         * @psalm-suppress PossiblyNullFunctionCall
         * @phpstan-ignore-next-line
         */
        $this->assertEquals('some_table_name', \call_user_func($function->getCallable(), ''));
    }

    public function testItGetsColumnName(): void
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
        $function  = \array_values(\array_filter($extension->getFunctions(), static function(TwigFunction $function) {
            return 'column_name' === $function->getName();
        }))[0];

        /**
         * @psalm-suppress PossiblyNullFunctionCall
         * @phpstan-ignore-next-line
         */
        $this->assertEquals('some_column_name', \call_user_func($function->getCallable(), '', ''));
    }

    public function testItGetsJoinTableName(): void
    {
        $this->markTestIncomplete('Missing implementation');
    }

    public function testItGetsJoinTableJoinColumns(): void
    {
        $this->markTestIncomplete('Missing implementation');
    }

    public function testItGetsJoinTableInverseJoinColumns(): void
    {
        $this->markTestIncomplete('Missing implementation');
    }

    public function testItGetsJoinTableJoinColumn(): void
    {
        $this->markTestIncomplete('Missing implementation');
    }

    public function testItGetsJoinTableInverseJoinColumn(): void
    {
        $this->markTestIncomplete('Missing implementation');
    }

    public function testItGetsPrimaryKeyColumnName(): void
    {
        $this->markTestIncomplete('Missing implementation');
    }
}
