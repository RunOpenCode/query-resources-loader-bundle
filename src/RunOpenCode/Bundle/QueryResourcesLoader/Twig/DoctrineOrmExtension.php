<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Twig;

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
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('table_name', \Closure::bind(function ($entity) {
                return $this->getTableName($entity);
            }, $this)),
            new TwigFunction('join_table_name', \Closure::bind(function ($field, $entity) {
                return $this->getJoinTableName($field, $entity);
            }, $this)),
            new TwigFunction('column_name', \Closure::bind(function ($field, $entity) {
                return $this->getColumnName($field, $entity);
            }, $this)),
            new TwigFunction('join_table_join_columns', \Closure::bind(function ($field, $entity) {
                return $this->getJoinTableJoinColumns($field, $entity);
            }, $this)),
            new TwigFunction('join_table_inverse_join_columns', \Closure::bind(function ($field, $entity) {
                return $this->getJoinTableInverseJoinColumns($field, $entity);
            }, $this)),
            new TwigFunction('join_table_join_column', \Closure::bind(function ($field, $entity) {
                return $this->getJoinTableJoinColumns($field, $entity)[0];
            }, $this)),
            new TwigFunction('join_table_inverse_join_column', \Closure::bind(function ($field, $entity) {
                return $this->getJoinTableInverseJoinColumns($field, $entity)[0];
            }, $this)),
            new TwigFunction('primary_key_column_name', \Closure::bind(function ($entity) {
                return $this->getPrimaryKeyColumnName($entity);
            }, $this)),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('table_name', \Closure::bind(function ($entity) {
                return $this->getTableName($entity);
            }, $this)),
            new TwigFilter('join_table_name', \Closure::bind(function ($field, $entity) {
                return $this->getJoinTableName($field, $entity);
            }, $this)),
            new TwigFilter('column_name', \Closure::bind(function ($field, $entity) {
                return $this->getColumnName($field, $entity);
            }, $this)),
            new TwigFilter('join_table_join_columns', \Closure::bind(function ($field, $entity) {
                return $this->getJoinTableJoinColumns($field, $entity);
            }, $this)),
            new TwigFilter('join_table_inverse_join_columns', \Closure::bind(function ($field, $entity) {
                return $this->getJoinTableInverseJoinColumns($field, $entity);
            }, $this)),
            new TwigFilter('join_table_join_column', \Closure::bind(function ($field, $entity) {
                return $this->getJoinTableJoinColumns($field, $entity)[0];
            }, $this)),
            new TwigFilter('join_table_inverse_join_column', \Closure::bind(function ($field, $entity) {
                return $this->getJoinTableInverseJoinColumns($field, $entity)[0];
            }, $this)),
            new TwigFilter('primary_key_column_name', \Closure::bind(function ($entity) {
                return $this->getPrimaryKeyColumnName($entity);
            }, $this)),
        ];
    }

    /**
     * Get table name for entity
     *
     * @param string $entity Entity
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
     * @param string $field  Entity property
     * @param string $entity Entity
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
     * @param string $field  Entity property
     * @param string $entity Entity
     *
     * @return string
     *
     * @throws MappingException
     */
    private function getJoinTableName(string $field, string $entity): string
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->doctrine->getManagerForClass($entity);
        /** @var ClassMetadataInfo $metadata */
        $metadata = $entityManager->getClassMetadata($entity);
        $mapping  = $metadata->getAssociationMapping($field);

        return $mapping['joinTable']['name'];
    }

    /**
     * Get join table join column names for property of the entity
     *
     * @param string $field  Entity property
     * @param string $entity Entity
     *
     * @return string[]
     *
     * @throws MappingException
     */
    private function getJoinTableJoinColumns(string $field, string $entity): array
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->doctrine->getManagerForClass($entity);
        /** @var ClassMetadataInfo $metadata */
        $metadata = $entityManager->getClassMetadata($entity);
        $mapping  = $metadata->getAssociationMapping($field);

        return $mapping['joinTable']['joinColumns'];
    }

    /**
     * Get join table inverse join column names for property of the entity
     *
     * @param string $field  Entity property
     * @param string $entity Entity
     *
     * @return string[]
     *
     * @throws MappingException
     */
    private function getJoinTableInverseJoinColumns(string $field, string $entity): array
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->doctrine->getManagerForClass($entity);
        /** @var ClassMetadataInfo $metadata */
        $metadata = $entityManager->getClassMetadata($entity);
        $mapping  = $metadata->getAssociationMapping($field);

        return $mapping['joinTable']['inverseJoinColumns'];
    }

    /**
     * Get primary column name of given entity.
     *
     * @param string $entity Entity
     *
     * @return string[]
     *
     * @throws RuntimeException If there is no primary key defined, or primary key is compound key.
     */
    private function getPrimaryKeyColumnName(string $entity): string
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->doctrine->getManagerForClass($entity);
        /** @var ClassMetadataInfo $metadata */
        $metadata              = $entityManager->getClassMetadata($entity);
        $identifierColumnNames = $metadata->getIdentifierColumnNames();

        if (0 === \count($identifierColumnNames)) {
            throw new RuntimeException(\sprintf(
                'Expected only one primary column for entity "%s", got "%s".',
                $entity,
                \count($identifierColumnNames)
            ));
        }

        return $identifierColumnNames[0];
    }
}
