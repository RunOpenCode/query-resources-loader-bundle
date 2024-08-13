<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Twig\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ManagerRegistry;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class DoctrineOrmExtension extends AbstractExtension
{
    private ?ManagerRegistry $doctrine;

    public function __construct(?ManagerRegistry $doctrine)
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
            new TwigFunction('table_name', $this->getTableName(...)),
            new TwigFunction('join_table_name', $this->getJoinTableName(...)),
            new TwigFunction('column_name', $this->getColumnName(...)),
            new TwigFunction('join_table_join_columns', $this->getJoinTableJoinColumns(...)),
            new TwigFunction('join_table_inverse_join_columns', $this->getJoinTableInverseJoinColumns(...)),
            new TwigFunction('join_table_join_column', $this->getJoinTableJoinColumn(...)),
            new TwigFunction('join_table_inverse_join_column', $this->getJoinTableInverseJoinColumn(...)),
            new TwigFunction('primary_key_column_name', $this->getPrimaryKeyColumnName(...)),
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
            new TwigFilter('table_name', $this->getTableName(...)),
            new TwigFilter('join_table_name', $this->getJoinTableName(...)),
            new TwigFilter('column_name', $this->getColumnName(...)),
            new TwigFilter('join_table_join_columns', $this->getJoinTableJoinColumns(...)),
            new TwigFilter('join_table_inverse_join_columns', $this->getJoinTableInverseJoinColumns(...)),
            new TwigFilter('join_table_join_column', $this->getJoinTableJoinColumn(...)),
            new TwigFilter('join_table_inverse_join_column', $this->getJoinTableInverseJoinColumn(...)),
            new TwigFilter('primary_key_column_name', $this->getPrimaryKeyColumnName(...)),
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
        return $this->getEntityManager($entity)->getClassMetadata($entity)->getTableName();
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
        return $this->getEntityManager($entity)->getClassMetadata($entity)->getColumnName($field);
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
        $metadata = $this->getEntityManager($entity)->getClassMetadata($entity);
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
        $metadata = $this->getEntityManager($entity)->getClassMetadata($entity);
        /** @var array{joinTable: array{joinColumns: string[]}} $mapping */
        $mapping = $metadata->getAssociationMapping($field);

        return $mapping['joinTable']['joinColumns'];
    }

    /**
     * Get join table join column name for property of the entity
     *
     * @param string       $field  Entity property
     * @param class-string $entity Entity
     * @param int          $index  Index of the column
     *
     * @return string
     *
     * @throws MappingException
     */
    private function getJoinTableJoinColumn(string $field, string $entity, int $index = 0): string
    {
        return \array_values($this->getJoinTableJoinColumns($field, $entity))[$index];
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
        $metadata = $this->getEntityManager($entity)->getClassMetadata($entity);
        /** @var array{joinTable: array{inverseJoinColumns: string[]}} $mapping */
        $mapping = $metadata->getAssociationMapping($field);

        return $mapping['joinTable']['inverseJoinColumns'];
    }

    /**
     * Get join table inverse join column name for property of the entity
     *
     * @param string       $field  Entity property
     * @param class-string $entity Entity
     * @param int          $index  Index of the column
     *
     * @return string
     *
     * @throws MappingException
     */
    private function getJoinTableInverseJoinColumn(string $field, string $entity, int $index = 0): string
    {
        return \array_values($this->getJoinTableInverseJoinColumns($field, $entity))[$index];
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
        $metadata              = $this->getEntityManager($entity)->getClassMetadata($entity);
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

    /**
     * Get entity manager for given entity.
     *
     * @param class-string $entity Entity
     *
     * @return EntityManagerInterface
     *
     * @throws RuntimeException If Doctrine is not available.
     */
    private function getEntityManager(string $entity): EntityManagerInterface
    {
        if (null === $this->doctrine) {
            throw new RuntimeException('Doctrine is not available, did you installed doctrine/orm package?');
        }

        /** @var EntityManagerInterface|null $entityManager */
        $entityManager = $this->doctrine->getManagerForClass($entity);

        if (null === $entityManager) {
            throw new RuntimeException(\sprintf('Entity manager for entity "%s" is not available.', $entity));
        }

        return $entityManager;
    }
}
