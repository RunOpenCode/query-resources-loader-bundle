<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

/**
 * Executor executes query in native environment.
 */
interface ExecutorInterface
{
    /**
     * Execute query.
     *
     * @param string                    $query      Query to execute.
     * @param array<string, mixed>      $parameters Parameters required for query.
     * @param array<string, string|int> $types      Parameter types required for query.
     * @param array<string, mixed>      $options    Any executor specific options (depending on concrete driver).
     *
     * @return ExecutionResultInterface Result of execution.
     */
    public function execute(string $query, array $parameters = [], array $types = [], array $options = []): ExecutionResultInterface;

    /**
     * Execute query and iterate results in batches.
     *
     * @param string                                                                                $query      Query to execute.
     * @param array<string, mixed>                                                                  $parameters Parameters required for query.
     * @param array<string, string|int>                                                             $types      Parameter types required for query.
     * @param array<string, mixed>&array{iterate?:string, batch_size?:int, on_batch_end?: callable} $options    Any executor specific options (depending on concrete driver).
     *
     * @return IterateResultInterface Result of execution.
     *
     * @see \RunOpenCode\Bundle\QueryResourcesLoader\Contract\IterateResultInterface::ITERATE_*
     */
    public function iterate(string $query, array $parameters = [], array $types = [], array $options = []): IterateResultInterface;
}
