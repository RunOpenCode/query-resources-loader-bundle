<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\MiddlewareInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExceptionInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;

/**
 * Query executor.
 *
 * Query executor finds appropriate executor from registry, applies
 * middlewares and execute the query.
 *
 * @phpstan-import-type MiddlewareFunction from \RunOpenCode\Bundle\QueryResourcesLoader\Contract\MiddlewareInterface
 *
 * @internal
 */
final readonly class QueryExecutor
{
    /**
     * @var callable(string, Parameters, Options): ExecutionResultInterface
     */
    private mixed $executor;

    /**
     * @param iterable<MiddlewareInterface|MiddlewareFunction> $middlewares
     */
    public function __construct(
        ExecutorsRegistry $registry,
        iterable          $middlewares = [],
    ) {
        // Last middleware is the executor.
        $executor = static fn(string $query, Parameters $parameters, Options $options): ExecutionResultInterface => $registry->get($options->executor)->execute($query, $parameters, $options);

        // Build middleware chain.
        foreach ($middlewares as $middleware) {
            $executor = static fn(string $query, Parameters $parameters, Options $options): ExecutionResultInterface => $middleware($query, $parameters, $options, $executor);
        }

        $this->executor = $executor;
    }

    /**
     * Execute a query.
     */
    public function execute(string $query, Parameters $parameters, Options $options): ExecutionResultInterface
    {
        try {
            return ($this->executor)($query, $parameters, $options);
        } catch (ExceptionInterface $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new RuntimeException(\sprintf(
                'Unable to execute query "%s".',
                $query,
            ), $exception);
        }
    }
}
