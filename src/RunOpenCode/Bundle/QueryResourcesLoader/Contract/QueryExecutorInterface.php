<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExceptionInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\TransactionException;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;

/**
 * Executor executes query in native environment.
 */
interface QueryExecutorInterface
{
    /**
     * Begin transaction.
     *
     * @param Options $options Executor specific options.
     *
     * @throws TransactionException If transaction could not be started.
     * @throws RuntimeException If unknown error occurred.
     */
    public function beginTransaction(Options $options): void;

    /**
     * Execute query.
     *
     * @param string     $query      Query to execute.
     * @param Parameters $parameters Parameters for query.
     * @param Options    $options    Executor specific options.
     *
     * @return ExecutionResultInterface Result of execution.
     *
     * @throws ExceptionInterface If execution fails. Wraps low level exception.
     * @throws RuntimeException If unknown error occurred.
     */
    public function execute(string $query, Parameters $parameters, Options $options): ExecutionResultInterface;

    /**
     * Commit transaction.
     *
     * @throws TransactionException If transaction could not be commited.
     * @throws RuntimeException If any other error occurred.
     */
    public function commit(): void;

    /**
     * Rollback transaction.
     *
     * @throws TransactionException If transaction could not be rolled back.
     * @throws RuntimeException If any other error occurred.
     */
    public function rollback(): void;
}
