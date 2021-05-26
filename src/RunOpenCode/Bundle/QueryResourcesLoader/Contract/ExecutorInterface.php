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
     * @param string                $query      Query to execute.
     * @param array<string, mixed>  $parameters Parameters required for query.
     * @param array<string, string> $types      Parameter types required for query.
     * @param array<string, mixed>  $options    Any executor specific options (depending on concrete driver).
     *
     * @return ExecutionResultInterface<mixed, mixed> Result of execution.
     */
    public function execute(string $query, array $parameters = [], array $types = [], array $options = []): ExecutionResultInterface;
}
