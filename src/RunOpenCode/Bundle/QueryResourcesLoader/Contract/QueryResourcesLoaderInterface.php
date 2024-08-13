<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExceptionInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExecutionException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\TransactionException;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;

/**
 * Query resources loader.
 *
 * Query resources loader is responsible for loading query resources from different sources
 * and executing them.
 */
interface QueryResourcesLoaderInterface
{
    /**
     * Execute query.
     *
     * @param string          $query      Query to execute.
     * @param Parameters|null $parameters Parameters for query (or null if no parameters are used).
     * @param Options|null    $options    Executor specific options (or null if default options should be used).
     *
     * @return ExecutionResultInterface Result of execution.
     *
     * @throws ExecutionException  If execution of query fails.
     * @throws ExceptionInterface If any other exception is thrown during execution of query.
     * @throws RuntimeException If unknown error occurred.
     */
    public function execute(string $query, ?Parameters $parameters = null, ?Options $options = null): ExecutionResultInterface;

    /**
     * Execute queries in transactional scope.
     *
     * This method should be used when you need to execute multiple queries in transactional scope. Transactional scope
     * will be created for either default executor or executors provided in options.
     *
     * Passed function will be executed in transactional scope and if any exception is thrown, transaction will be
     * rolled back for all executors stated in options (or only for default executor if none provided).
     *
     * @template T
     *
     * @param callable(QueryResourcesLoaderInterface): T $callable   A function which should be executed in transactional scope.
     * @param Options                                    ...$options Options for executors which wraps callable in transactional scope.
     *                                                               If none provided, default options for default executor will be used.
     *
     * @return T Result of transactional scope.
     *
     * @throws TransactionException If transaction could not be started, commited or rolled back.
     * @throws ExecutionException  If execution of queries in transactional scope fails.
     * @throws ExceptionInterface If any other exception is thrown during execution of transactional scope.
     * @throws RuntimeException If unknown error occurred.
     */
    public function transactional(callable $callable, Options ...$options): mixed;
}
