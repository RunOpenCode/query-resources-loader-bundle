<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Manager;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\LoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExceptionInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\IterateResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExecutionException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SourceNotFoundException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SyntaxException;

/**
 * Default query executor.
 */
final class DefaultManager implements ManagerInterface
{
    private LoaderInterface $loader;

    /**
     * @var array<string, ExecutorInterface>
     */
    private array $executors;

    /**
     * @param array<string, ExecutorInterface> $executors
     */
    public function __construct(LoaderInterface $loader, iterable $executors = [])
    {
        $this->loader    = $loader;
        $this->executors = [];

        foreach ($executors as $name => $executor) {
            $this->registerExecutor($executor, $name);
        }
    }

    /**
     * Register query executor.
     *
     * @param ExecutorInterface $executor
     * @param string            $name
     */
    public function registerExecutor(ExecutorInterface $executor, string $name): void
    {
        $this->executors[$name] = $executor;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return $this->loader->has($name);
    }

    /**
     * {@inheritdoc}
     *
     * @throws SourceNotFoundException
     * @throws SyntaxException
     * @throws RuntimeException
     */
    public function get(string $name, array $args = []): string
    {
        return $this->loader->get($name, $args);
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     * @throws ExecutionException
     */
    public function execute(string $name, array $args = [], array $types = [], array $options = [], ?string $executor = null): ExecutionResultInterface
    {
        /** @psalm-suppress PossiblyNullArrayOffset */
        $instance = $this->executors[$executor ?? \array_key_first($this->executors)] ?? null;

        if (null === $instance) {
            throw new RuntimeException(null !== $executor ? \sprintf('Executor "%s" does not exists.', $executor) : 'There are no registered executors.');
        }

        try {
            return $instance->execute($this->get($name, $args), $args, $types, $options);
        } catch (\Exception $exception) {
            if ($exception instanceof ExceptionInterface) {
                throw $exception;
            }

            throw new ExecutionException(\sprintf(
                'Query "%s" could not be executed.',
                $name
            ), $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function transactional(\Closure $callback, array $options = [], ?string $executor = null): void
    {
        /** @psalm-suppress PossiblyNullArrayOffset */
        $instance = $this->executors[$executor ?? \array_key_first($this->executors)] ?? null;

        if (null === $instance) {
            throw new RuntimeException(null !== $executor ? \sprintf('Executor "%s" does not exists.', $executor) : 'There are no registered executors.');
        }
        
        // begin
        $callback($this);
        // commit
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     *
     * @param array{
     *     iterate?: string,
     *     batch_size?: int,
     *     on_batch_end?: callable
     * } $options
     *
     * @throws RuntimeException
     * @throws ExecutionException
     */
    public function iterate(string $name, array $args = [], array $types = [], array $options = [], ?string $executor = null): IterateResultInterface
    {
        /** @psalm-suppress PossiblyNullArrayOffset */
        $instance = $this->executors[$executor ?? \array_key_first($this->executors)] ?? null;

        if (null === $instance) {
            throw new RuntimeException(null !== $executor ? \sprintf('Executor "%s" does not exists.', $executor) : 'There are no registered executors.');
        }

        try {
            return $instance->iterate($this->get($name, $args), $args, $types, $options);
        } catch (\Exception $exception) {
            if ($exception instanceof ExceptionInterface) {
                throw $exception;
            }

            throw new ExecutionException(\sprintf(
                'Query "%s" could not be executed.',
                $name
            ), $exception);
        }
    }
}
