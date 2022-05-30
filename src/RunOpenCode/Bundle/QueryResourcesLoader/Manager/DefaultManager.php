<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Manager;

use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExceptionInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\IterateResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExecutionException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;

/**
 * Default query executor.
 */
final class DefaultManager implements ManagerInterface
{
    /**
     * @var array<string, ExecutorInterface>
     */
    private array $executors;

    /**
     * @param array<string, ExecutorInterface> $executors
     */
    public function __construct(iterable $executors = [])
    {
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
     *
     * @throws RuntimeException
     * @throws ExecutionException
     */
    public function execute(string $name, array $args = [], array $types = [], ?string $executor = null): ExecutionResultInterface
    {
        /**
         * @psalm-suppress PossiblyNullArrayOffset, UnnecessaryVarAnnotation
         *
         * @var ExecutorInterface|null $instance
         */
        $instance = $this->executors[$executor ?? \array_key_first($this->executors)] ?? null;

        if (null === $instance) {
            throw new RuntimeException(null !== $executor ? \sprintf('Executor "%s" does not exists.', $executor) : 'There are no registered executors.');
        }

        try {
            return $instance->execute($name, $args, $types);
        } catch (ExceptionInterface $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new ExecutionException(\sprintf(
                'Unable to execute query "%s".',
                $name,
            ), $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function transactional(\Closure $scope, array $options = [], ?string $executor = null): void
    {
        /**
         * @psalm-suppress PossiblyNullArrayOffset, UnnecessaryVarAnnotation
         *
         * @var ExecutorInterface|null $instance
         */
        $instance = $this->executors[$executor ?? \array_key_first($this->executors)] ?? null;

        if (null === $instance) {
            throw new RuntimeException(null !== $executor ? \sprintf('Executor "%s" does not exists.', $executor) : 'There are no registered executors.');
        }

        try {
            $instance->transactional($scope, $options);
        } catch (ExceptionInterface $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new ExecutionException('Unable to execute transaction.', $exception);
        }
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
        /**
         * @psalm-suppress PossiblyNullArrayOffset, UnnecessaryVarAnnotation
         *
         * @var ExecutorInterface|null $instance
         */
        $instance = $this->executors[$executor ?? \array_key_first($this->executors)] ?? null;

        if (null === $instance) {
            throw new RuntimeException(null !== $executor ? \sprintf('Executor "%s" does not exists.', $executor) : 'There are no registered executors.');
        }

        try {
            return $instance->iterate($name, $args, $types, $options);
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
