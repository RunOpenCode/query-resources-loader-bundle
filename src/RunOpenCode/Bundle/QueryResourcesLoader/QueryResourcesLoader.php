<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\LogicException;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\ExecutorsRegistry;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\QueryExecutor;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;

final readonly class QueryResourcesLoader implements QueryResourcesLoaderInterface
{
    public function __construct(
        private ExecutorsRegistry $registry,
        private QueryExecutor     $executor,
    ) {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $query, ?Parameters $parameters = null, ?Options $options = null): ExecutionResultInterface
    {
        $parameters = $parameters ?? Parameters::create();
        $options    = $options ?? Options::create();

        return $this->executor->execute($query, $parameters, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function transactional(callable $callable, Options ...$options): mixed
    {
        $options   = 0 === \count($options) ? [Options::create()] : $options;
        $executors = \array_unique(\array_map(static fn(Options $option): ?string => $option->executor, $options));

        if (\count($executors) !== \count($options)) {
            throw new LogicException('You cannot use same executor multiple time within same transaction scope.');
        }

        try {
            foreach ($options as $option) {
                $this->registry->get($option->executor)->beginTransaction($option);
            }

            $result = $callable($this);

            foreach ($options as $option) {
                $this->registry->get($option->executor)->commit();
            }

            return $result;

        } catch (\Exception $exception) {
            foreach ($options as $option) {
                $this->registry->get($option->executor)->rollback();
            }

            throw $exception;
        }
    }
}
