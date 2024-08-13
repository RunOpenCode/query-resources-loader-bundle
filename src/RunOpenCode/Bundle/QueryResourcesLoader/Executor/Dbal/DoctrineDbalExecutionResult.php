<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal;

use Doctrine\DBAL\Cache\ArrayResult;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Result as ExecutionResult;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NonUniqueResultException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NoResultException;

/**
 * Doctrine Dbal executor result statement wrapper that provides you with useful methods when fetching results from
 * SELECT statement.
 *
 * @implements \IteratorAggregate<array-key, mixed>
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 *
 * @psalm-suppress InternalMethod, InternalClass
 */
final readonly class DoctrineDbalExecutionResult implements \IteratorAggregate, ExecutionResultInterface, Result
{
    private ResultProxy $result;

    public function __construct(ExecutionResult|ArrayResult $result)
    {
        $this->result = new ResultProxy($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getSingleScalarResult(): mixed
    {
        $scalar = $this->result->fetchOne();

        /** @var scalar|false $scalar */
        if (false === $scalar) {
            throw new NoResultException('Expected on result for given query.');
        }

        if (false !== $this->result->fetchOne()) {
            throw new NonUniqueResultException('Expected only one result for given query.');
        }

        return $scalar;
    }

    /**
     * {@inheritdoc}
     */
    public function getSingleScalarResultOrDefault(mixed $default): mixed
    {
        try {
            return $this->getSingleScalarResult();
        } catch (NoResultException) {
            return $default;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSingleScalarResultOrNull(): mixed
    {
        return $this->getSingleScalarResultOrDefault(null);
    }

    /**
     * {@inheritdoc}
     */
    public function getScalarResult(): array
    {
        $result = [];

        while ($val = $this->result->fetchOne()) {
            $result[] = $val;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getScalarResultOrDefault(mixed $default): mixed
    {
        $result = $this->getScalarResult();

        if (0 === \count($result)) {
            return $default;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getScalarResultOrNull(): mixed
    {
        return $this->getScalarResultOrDefault(null);
    }

    /**
     * {@inheritdoc}
     */
    public function getSingleResult(): array
    {
        $row = $this->result->fetchAssociative();

        if (false === $row) {
            throw new NoResultException('Expected on result for given query.');
        }

        if (false !== $this->result->fetchAssociative()) {
            throw new NonUniqueResultException('Expected only one result for given query.');
        }

        return $row;
    }

    /**
     * {@inheritdoc}
     */
    public function getSingleResultOrDefault(mixed $default): mixed
    {
        try {
            return $this->getSingleResult();
        } catch (NoResultException $e) {
            return $default;
        }
    }

    public function getSingleResultOrNull(): mixed
    {
        return $this->getSingleResultOrDefault(null);
    }

    /**
     * {@inheritdoc}
     */
    public function columnCount(): int
    {
        return $this->result->columnCount();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAssociative(): array|false
    {
        return $this->result->fetchAssociative();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchNumeric(): array|false
    {
        return $this->result->fetchNumeric();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchOne(): mixed
    {
        return $this->result->fetchOne();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAllNumeric(): array
    {
        return $this->result->fetchAllNumeric();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAllAssociative(): array
    {
        return $this->result->fetchAllAssociative();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchFirstColumn(): array
    {
        return $this->result->fetchFirstColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function free(): void
    {
        $this->result->free();
    }

    /**
     * {@inheritdoc}
     */
    public function rowCount(): int
    {
        return $this->result->rowCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        while (false !== ($row = $this->result->fetchAssociative())) {
            yield $row;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->result->count();
    }
}
