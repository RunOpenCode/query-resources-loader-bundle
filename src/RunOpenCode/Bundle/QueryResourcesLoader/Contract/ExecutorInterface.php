<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

/**
 * Executor is preserved as compatibility layer for older versions.
 *
 * Method 'iterate()' is removed from this interface, as it is recommended
 * to use either RxPHP or some other library for streaming results.
 *
 * Use \RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryExecutorInterface instead.
 *
 * @see QueryExecutorInterface
 *
 * @deprecated
 */
interface ExecutorInterface
{
    /**
     * Execute query.
     *
     * @param string                              $name       Name of Query source code.
     * @param array<string, mixed>                $parameters Arguments for modification/compilation of Query source code, as well as params for query statement.
     * @param array<string, string|int|\UnitEnum> $types      Parameter types required for query.
     *
     * @return ExecutionResultInterface Result of execution.
     */
    public function execute(string $name, array $parameters = [], array $types = []): ExecutionResultInterface;

    /**
     * Create transactional scope and execute queries within single transaction.
     *
     * @template T
     *
     * @param \Closure(ExecutorInterface $executor): T $scope   Closure to invoke in order to execute statements within transaction scope.
     * @param array<string, mixed>                     $options Any executor specific options (depending on concrete driver).
     *
     * @return T Result of transactional scope.
     */
    public function transactional(\Closure $scope, array $options = []): mixed;
}
