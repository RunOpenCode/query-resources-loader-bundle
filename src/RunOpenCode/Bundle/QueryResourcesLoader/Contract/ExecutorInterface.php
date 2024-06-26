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
    public function transactional(\Closure $scope, array $options = []);

    /**
     * Execute query and iterate results in batches.
     *
     * @param string                                                                                $name       Name of Query source code.
     * @param array<string, mixed>                                                                  $parameters Arguments for modification/compilation of Query source code, as well as params for query statement.
     * @param array<string, string|int>                                                             $types      Parameter types required for query.
     * @param array<string, mixed>&array{iterate?:string, batch_size?:int, on_batch_end?: callable} $options    Any executor specific options (depending on concrete driver).
     *
     * @return IterateResultInterface Result of execution.
     *
     * @see        \RunOpenCode\Bundle\QueryResourcesLoader\Contract\IterateResultInterface::ITERATE_*
     *
     * @deprecated Use https://github.com/ReactiveX/RxPHP for buffering and batching results.
     */
    public function iterate(string $name, array $parameters = [], array $types = [], array $options = []): IterateResultInterface;
}
