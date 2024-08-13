<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExceptionInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;

/**
 * Execution middleware.
 *
 * Execution of the query is done by middleware, which are executed in chain.
 *
 * Each middleware needs to implement this interface. Last middleware in chain
 * is responsible for execution of the query and will not call next middleware.
 *
 * @phpstan-type Next = callable(string, Parameters, Options): ExecutionResultInterface
 * @phpstan-type MiddlewareFunction = callable(string, Parameters, Options, ?Next): ExecutionResultInterface
 */
interface MiddlewareInterface
{
    /**
     * Execute query middleware.
     *
     * @param string     $query      Query to execute.
     * @param Parameters $parameters Parameters for query.
     * @param Options    $options    Executor specific options.
     * @param Next       $next       Next middleware in chain.
     *
     * @return ExecutionResultInterface
     *
     * @throws ExceptionInterface If execution fails. Wraps low level exception.
     * @throws RuntimeException If unknown error occurred.
     */
    public function __invoke(string $query, Parameters $parameters, Options $options, callable $next): ExecutionResultInterface;
}
