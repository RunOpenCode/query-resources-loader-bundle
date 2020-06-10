<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ManagerRegistry;
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
}
