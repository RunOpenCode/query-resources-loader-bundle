<?php

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NonUniqueResultException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NoResultException;

/**
 * Execution result.
 *
 * @extends \Traversable<mixed, mixed>
 */
interface ExecutionResultInterface extends \Traversable, \Countable
{
    /**
     * Get single scalar result.
     *
     * @return mixed A single scalar value.
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getSingleScalarResult();

    /**
     * Get single scalar result or default value if there are no results of executed
     * SELECT statement.
     *
     * @param mixed $default A default single scalar value.
     *
     * @return mixed A single scalar value.
     *
     * @throws NonUniqueResultException
     */
    public function getSingleScalarResultOrDefault($default);

    /**
     * Get single scalar result or NULL value if there are no results of executed
     * SELECT statement.
     *
     * @return mixed|null A single scalar value.
     */
    public function getSingleScalarResultOrNull();

    /**
     * Get collection of scalar values.
     *
     * @return array<mixed> A collection of scalar values.
     */
    public function getScalarResult();

    /**
     * Get collection of scalar vales, or default value if collection is empty.
     *
     * @param mixed $default A default value.
     *
     * @return array<mixed>|mixed A collection of scalar values or default value.
     */
    public function getScalarResultOrDefault($default);

    /**
     * Get collection of scalar vales, or NULL value if collection is empty.
     *
     * @return array<mixed>|mixed|null A collection of NULL value.
     */
    public function getScalarResultOrNull();

    /**
     * Get single (first) row result from result set.
     *
     * @return array<mixed>|mixed A single (first) row of result set.
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getSingleResult();

    /**
     * Get single (first) row result from result set or default value if result set is empty.
     *
     * @param mixed $default Default value if result set is empty.
     *
     * @return array<mixed>|mixed A single (first) row of result set.
     *
     * @throws NonUniqueResultException
     */
    public function getSingleResultOrDefault($default);

    /**
     * Get single (first) row result from result set or NULL value if result set is empty.
     *
     * @return array<mixed>|mixed|null A single (first) row of result set.
     *
     * @throws NonUniqueResultException
     */
    public function getSingleResultOrNull();
}
