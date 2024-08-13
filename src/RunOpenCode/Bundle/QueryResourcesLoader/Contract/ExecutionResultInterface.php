<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

use RunOpenCode\Bundle\QueryResourcesLoader\Exception\DriverException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NonUniqueResultException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NoResultException;

/**
 * Execution result.
 *
 * Execution result is a result of executed selection query
 * and concrete implementation must be serializable.
 *
 * @extends \Traversable<array-key, mixed>
 */
interface ExecutionResultInterface extends \Traversable, \Countable
{
    /**
     * Get single scalar result.
     *
     * @return scalar A single scalar value.
     *
     * @throws NoResultException If there are no results of executed SELECT statement.
     * @throws NonUniqueResultException If there are more than one result of executed SELECT statement.
     * @throws DriverException If there is a underlying driver error.
     */
    public function getSingleScalarResult(): mixed;

    /**
     * Get single scalar result or default value if there are no results of executed
     * SELECT statement.
     *
     * @template T
     *
     * @param T $default A default single scalar value.
     *
     * @return mixed|T A single scalar value.
     *
     * @throws NonUniqueResultException If there are more than one result of executed SELECT statement.
     * @throws DriverException If there is a underlying driver error.
     */
    public function getSingleScalarResultOrDefault(mixed $default): mixed;

    /**
     * Get single scalar result or NULL value if there are no results of executed
     * SELECT statement.
     *
     * @return mixed|null A single scalar value.
     *
     * @throws NonUniqueResultException If there are more than one result of executed SELECT statement.
     * @throws DriverException If there is a underlying driver error.
     */
    public function getSingleScalarResultOrNull(): mixed;

    /**
     * Get collection of scalar values.
     *
     * @return mixed[] A collection of scalar values.
     *
     * @throws DriverException If there is a underlying driver error.
     */
    public function getScalarResult(): array;

    /**
     * Get collection of scalar vales, or default value if collection is empty.
     *
     * @template T
     *
     * @param T $default A default value.
     *
     * @return mixed[]|T A collection of scalar values or default value.
     *
     * @throws DriverException If there is a underlying driver error.
     */
    public function getScalarResultOrDefault(mixed $default): mixed;

    /**
     * Get collection of scalar vales, or NULL value if collection is empty.
     *
     * @return mixed[]|null A collection of NULL value.
     *
     * @throws DriverException If there is a underlying driver error.
     */
    public function getScalarResultOrNull(): mixed;

    /**
     * Get single (first) row result from result set.
     *
     * @return array<array-key, mixed> A single (first) row of result set.
     *
     * @throws NoResultException If there are no results of executed SELECT statement.
     * @throws NonUniqueResultException If there are more than one result of executed SELECT statement.
     * @throws DriverException If there is a underlying driver error.
     */
    public function getSingleResult(): array;

    /**
     * Get single (first) row result from result set or default value if result set is empty.
     *
     * @template T
     *
     * @param T $default Default value if result set is empty.
     *
     * @return array<array-key, mixed>|T A single (first) row of result set.
     *
     * @throws NonUniqueResultException
     * @throws DriverException If there is a underlying driver error.
     */
    public function getSingleResultOrDefault(mixed $default): mixed;

    /**
     * Get single (first) row result from result set or NULL value if result set is empty.
     *
     * @return array<array-key, mixed>|null A single (first) row of result set.
     *
     * @throws NonUniqueResultException
     * @throws DriverException If there is a underlying driver error.
     */
    public function getSingleResultOrNull(): mixed;
}
