<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExecutionException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;

/**
 * Manager is preserved as compatibility layer for older versions.
 *
 * Method 'iterate()' is removed from this interface, as it is recommended
 * to use either RxPHP or some other library for streaming results.
 *
 * Use \RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface instead.
 *
 * @see QueryResourcesLoaderInterface
 *
 * @deprecated
 */
interface ManagerInterface
{
    /**
     * Execute Query source.
     *
     * @param string                    $name       Name of Query source code.
     * @param array<string, mixed>      $parameters Arguments for modification/compilation of Query source code, as well as params for query statement.
     * @param array<string, string|int> $types      Types of parameters for prepared statement.
     * @param null|string               $executor   Executor name.
     *
     * @return ExecutionResultInterface Execution results.
     *
     * @throws ExecutionException If execution fails. Wraps low level exception.
     * @throws RuntimeException If any other error occurred.
     */
    public function execute(string $name, array $parameters = [], array $types = [], ?string $executor = null): ExecutionResultInterface;

    /**
     * Create transactional scope and execute queries within single transaction.
     *
     * @template T
     *
     * @param \Closure(ExecutorInterface $executor): T $scope    Closure to invoke in order to execute statements within transaction scope.
     * @param array<string, mixed>                     $options  Any executor specific options (depending on concrete driver).
     * @param null|string                              $executor Executor name.
     *
     * @return T Result of transactional scope.
     * @throws RuntimeException If any other error occurred.
     *
     * @throws ExecutionException If execution fails. Wraps low level exception.
     * */
    public function transactional(\Closure $scope, array $options = [], ?string $executor = null);
}
