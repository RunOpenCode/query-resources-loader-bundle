<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

/**
 * Manager service provides Query source code from loaders, modifying it, if needed, as per concrete implementation of
 * relevant manager and supported scripting language. Manager can execute a Query as well.
 */
interface ManagerInterface
{
    /**
     * Execute Query source.
     *
     * @param string                    $name     Name of Query source code.
     * @param array<string, mixed>      $args     Arguments for modification/compilation of Query source code, as well as params for query statement.
     * @param array<string, string|int> $types    Types of parameters for prepared statement.
     * @param null|string               $executor Executor name.
     *
     * @return ExecutionResultInterface Execution results.
     */
    public function execute(string $name, array $args = [], array $types = [], ?string $executor = null): ExecutionResultInterface;

    /**
     * Create transactional scope and execute queries within single transaction.
     *
     * @param \Closure(ExecutorInterface $executor): void $scope Closure to invoke in order to execute statements within transaction scope.
     * @param array<string, mixed> $options  Any executor specific options (depending on concrete driver).
     * @param null|string          $executor Executor name.
     *
     * @return void
     */
    public function transactional(\Closure $scope, array $options = [], ?string $executor = null): void;

    /**
     * Execute query and iterate results in batches.
     *
     * Query is modified in order to accommodate LIMIT/OFFSET clauses,
     * provided query must not contain mentioned statements. Purpose is to
     * iterate rows without using table/database cursor and achieving small
     * memory footprint on both application and database side.
     *
     * Options may contain additional keys, depending on concrete driver,
     * but all contains the following:
     *
     * - iterate: string, how values should be yielded for each row.
     * - batch_size: int, how many rows to process per query.
     * - on_batch_end: callable, callable to invoke when batch is fully processed.
     *
     * Executor may provide for prepared statement "last_batch_row" with last row
     * of previous batch which may be used for building of query for next batch.
     *
     * @param string                                                                                $name    Name of Query source code.
     * @param array<string, mixed>                                                                  $args    Arguments for modification/compilation of Query source code, as well as params for query statement.
     * @param array<string, string|int>                                                             $types   Parameter types required for query.
     * @param array<string, mixed>|array{iterate?:string, batch_size?:int, on_batch_end?: callable} $options Any executor specific options (depending on concrete driver).
     *
     * @return IterateResultInterface Result of execution.
     *
     * @see \RunOpenCode\Bundle\QueryResourcesLoader\Contract\IterateResultInterface::ITERATE_*
     */
    public function iterate(string $name, array $args = [], array $types = [], array $options = [], ?string $executor = null): IterateResultInterface;
}
