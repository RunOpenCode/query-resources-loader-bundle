<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryExecutorInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;

/**
 * Registry of all executors.
 *
 * @internal
 */
final readonly class ExecutorsRegistry
{
    /**
     * @var array<string, QueryExecutorInterface>
     */
    private array $registry;

    /**
     * @param iterable<string, QueryExecutorInterface> $executors
     */
    public function __construct(iterable $executors = [])
    {
        $registry = [];

        foreach ($executors as $name => $executor) {
            $registry[$name] = $executor;
        }

        $this->registry = $registry;
    }

    /**
     * Get executor by name.
     *
     * @param string|null $name Executor name or default if not provided.
     *
     * @return QueryExecutorInterface
     *
     * @throws RuntimeException If executor does not exists.
     */
    public function get(?string $name = null): QueryExecutorInterface
    {
        if (null === $name) {
            return \array_values($this->registry)[0];
        }

        return $this->registry[$name] ?? throw new RuntimeException(\sprintf('Executor "%s" does not exists.', $name));
    }
}
