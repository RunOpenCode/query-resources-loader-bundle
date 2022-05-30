<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Twig\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ManagerRegistry;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class DoctrineOrmExtension extends AbstractExtension
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('table_name', \Closure::bind(function (string $entity): string {
                return $this->getTableName($entity); // @phpstan-ignore-line
            }, $this)),
            new TwigFunction('join_table_name', \Closure::bind(function (string $field, string $entity): string {
                return $this->getJoinTableName($field, $entity); // @phpstan-ignore-line
            }, $this)),
            new TwigFunction('column_name', \Closure::bind(function (string $field, string $entity): string {
                return $this->getColumnName($field, $entity); // @phpstan-ignore-line
            }, $this)),
            new TwigFunction('join_table_join_columns', \Closure::bind(function (string $field, string $entity): array {
                return $this->getJoinTableJoinColumns($field, $entity); // @phpstan-ignore-line
            }, $this)),
            new TwigFunction('join_table_inverse_join_columns', \Closure::bind(function (string $field, string $entity): array {
                return $this->getJoinTableInverseJoinColumns($field, $entity); // @phpstan-ignore-line
            }, $this)),
            new TwigFunction('join_table_join_column', \Closure::bind(function (string $field, string $entity): string {
                return $this->getJoinTableJoinColumns($field, $entity)[0]; // @phpstan-ignore-line
            }, $this)),
            new TwigFunction('join_table_inverse_join_column', \Closure::bind(function (string $field, string $entity): string {
                return $this->getJoinTableInverseJoinColumns($field, $entity)[0]; // @phpstan-ignore-line
            }, $this)),
            new TwigFunction('primary_key_column_name', \Closure::bind(function (string $entity): string {
                return $this->getPrimaryKeyColumnName($entity); // @phpstan-ignore-line
            }, $this)),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('table_name', \Closure::bind(function (string $entity): string {
                return $this->getTableName($entity); // @phpstan-ignore-line
            }, $this)),
            new TwigFilter('join_table_name', \Closure::bind(function (string $field, string $entity): string {
                return $this->getJoinTableName($field, $entity); // @phpstan-ignore-line
            }, $this)),
            new TwigFilter('column_name', \Closure::bind(function (string $field, string $entity): string {
                return $this->getColumnName($field, $entity); // @phpstan-ignore-line
            }, $this)),
            new TwigFilter('join_table_join_columns', \Closure::bind(function (string $field, string $entity): array {
                return $this->getJoinTableJoinColumns($field, $entity); // @phpstan-ignore-line
            }, $this)),
            new TwigFilter('join_table_inverse_join_columns', \Closure::bind(function (string $field, string $entity): array {
                return $this->getJoinTableInverseJoinColumns($field, $entity); // @phpstan-ignore-line
            }, $this)),
            new TwigFilter('join_table_join_column', \Closure::bind(function (string $field, string $entity): string {
                return $this->getJoinTableJoinColumns($field, $entity)[0]; // @phpstan-ignore-line
            }, $this)),
            new TwigFilter('join_table_inverse_join_column', \Closure::bind(function (string $field, string $entity): string {
                return $this->getJoinTableInverseJoinColumns($field, $entity)[0]; // @phpstan-ignore-line
            }, $this)),
            new TwigFilter('primary_key_column_name', \Closure::bind(function (string $entity): string {
                return $this->getPrimaryKeyColumnName($entity); // @phpstan-ignore-line
            }, $this)),
        ];
    }

    /**
     * Get table name for entity
     *
     * @param class-string $entity Entity
     *
     * @return string
     */
    private function getTableName(string $entity): string
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->doctrine->getManagerForClass($entity);

        return $entityManager->getClassMetadata($entity)->getTableName();
    }

    /**
     * Get column name for property of the entity
     *
     * @param string       $field  Entity property
     * @param class-string $entity Entity
     *
     * @return string
     */
    private function getColumnName(string $field, string $entity): string
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->doctrine->getManagerForClass($entity);

        return $entityManager->getClassMetadata($entity)->getColumnName($field);
    }

    /**
     * Get join table name name for property of the entity
     *
     * @param string       $field  Entity property
     * @param class-string $entity Entity
     *
     * @return string
     *
     * @throws MappingException
     */
    private function getJoinTableName(string $field, string $entity): string
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->doctrine->getManagerForClass($entity);
        /** @var ClassMetadataInfo<object> $metadata */
        $metadata = $entityManager->getClassMetadata($entity);
        /** @var array{joinTable: array{name: string}} $mapping */
        $mapping = $metadata->getAssociationMapping($field);

        return $mapping['joinTable']['name'];
    }

    /**
     * Get join table join column names for property of the entity
     *
     * @param string       $field  Entity property
     * @param class-string $entity Entity
     *
     * @return string[]
     *
     * @throws MappingException
     */
    private function getJoinTableJoinColumns(string $field, string $entity): array
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->doctrine->getManagerForClass($entity);
        /** @var ClassMetadataInfo<object> $metadata */
        $metadata = $entityManager->getClassMetadata($entity);
        /** @var array{joinTable: array{joinColumns: string[]}} $mapping */
        $mapping = $metadata->getAssociationMapping($field);

        return $mapping['joinTable']['joinColumns'];
    }

    /**
     * Get join table inverse join column names for property of the entity
     *
     * @param string       $field  Entity property
     * @param class-string $entity Entity
     *
     * @return string[]
     *
     * @throws MappingException
     */
    private function getJoinTableInverseJoinColumns(string $field, string $entity): array
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->doctrine->getManagerForClass($entity);
        /** @var ClassMetadataInfo<object> $metadata */
        $metadata = $entityManager->getClassMetadata($entity);
        /** @var array{joinTable: array{inverseJoinColumns: string[]}} $mapping */
        $mapping = $metadata->getAssociationMapping($field);

        return $mapping['joinTable']['inverseJoinColumns'];
    }

    /**
     * Get primary column name of given entity.
     *
     * @param class-string $entity Entity
     *
     * @throws RuntimeException If there is no primary key defined, or primary key is compound key.
     */
    private function getPrimaryKeyColumnName(string $entity): string
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->doctrine->getManagerForClass($entity);
        /** @var ClassMetadataInfo<object> $metadata */
        $metadata              = $entityManager->getClassMetadata($entity);
        $identifierColumnNames = $metadata->getIdentifierColumnNames();

        if (1 !== \count($identifierColumnNames)) {
            throw new RuntimeException(\sprintf(
                'Expected only one primary column for entity "%s", got "%s".',
                $entity,
                \count($identifierColumnNames)
            ));
        }

        return $identifierColumnNames[0];
    }
}
