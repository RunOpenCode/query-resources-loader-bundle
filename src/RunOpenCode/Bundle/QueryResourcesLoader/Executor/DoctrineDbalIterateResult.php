<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\IterateResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\LogicException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @implements \IteratorAggregate<mixed, mixed>
 */
final class DoctrineDbalIterateResult implements \IteratorAggregate, IterateResultInterface
{
    private DoctrineDbalExecutor $executor;

    private string $query;

    private array $parameters;

    private array $types;

    /**
     * @var array{
     *     iterate: string,
     *     batch_size: int,
     *     on_batch_end: callable
     * }
     */
    private array $options;

    /**
     * @param array<string, mixed>      $parameters
     * @param array<string, string|int> $types
     * @param array{
     *     iterate?: string,
     *     batch_size?: int,
     *     on_batch_end?: callable
     *     } $options                $options
     */
    public function __construct(
        DoctrineDbalExecutor $executor,
        string               $query,
        array                $parameters,
        array                $types,
        array                $options
    ) {
        if (\array_key_exists('last_batch_row', $parameters)) {
            throw new LogicException('Parameter "last_batch_row" is reserved and may not be used.');
        }

        $this->executor   = $executor;
        $this->query      = \rtrim(\trim($query), ';');
        $this->parameters = $parameters;
        $this->types      = $types;
        $this->options    = $this->resolveOptions($options);
    }

    /**
     * @return \Generator<mixed, mixed>
     */
    public function getIterator(): \Generator
    {
        $batchCount   = 0;
        $options      = $this->options;
        $batchSize    = $options['batch_size'];
        $onBatchEnd   = $options['on_batch_end'];
        $iterate      = $options['iterate'];
        $hasMore      = true;
        $lastBatchRow = null;
        $totalYield   = 0;

        unset($options['batch_size'], $options['on_batch_end'], $options['iterate']);

        while ($hasMore) {
            $count      = 0;
            $limit      = $batchSize + 1;
            $offset     = $batchCount * $batchSize;
            $hasMore    = false;
            $parameters = \array_merge($this->parameters, ['last_batch_row' => $lastBatchRow]);
            $query      = \sprintf('%s LIMIT %s OFFSET %s', $this->query, $limit, $offset);
            $result     = $this->executor->execute($query, $parameters, $this->types, $options);

            foreach ($result as $row) {
                $count++;

                if ($count > $batchSize) {
                    $lastBatchRow = $row;
                    $hasMore      = true;

                    $batchCount++;
                    $onBatchEnd();
                    break;
                }

                $totalYield++;

                if ($iterate === IterateResultInterface::ITERATE_COLUMN) {
                    yield \array_values($row)[0];
                    continue;
                }

                yield $row;
            }
        }

        if (0 !== $totalYield % $batchSize) {
            $onBatchEnd();
        }
    }

    /**
     * @param array{
     *     iterate?: string,
     *     batch_size?: int,
     *     on_batch_end?: callable
     *     } $options
     *
     * @return array{
     *     iterate: string,
     *     batch_size: int,
     *     on_batch_end: callable
     * } $options
     */
    private function resolveOptions(array $options): array
    {
        /** @var OptionsResolver|null $resolver */
        static $resolver;

        if (null === $resolver) {
            $resolver = new OptionsResolver();

            $resolver->setDefault('iterate', IterateResultInterface::ITERATE_ROW);
            $resolver->setAllowedValues('iterate', [
                IterateResultInterface::ITERATE_ROW,
                IterateResultInterface::ITERATE_COLUMN,
            ]);

            $resolver->setDefault('batch_size', 100);
            $resolver->setAllowedTypes('batch_size', 'int');
            $resolver->setAllowedValues('batch_size', static function (int $value): bool {
                return $value > 0;
            });

            $resolver->setDefault('on_batch_end', null);
            $resolver->setAllowedTypes('on_batch_end', ['null', 'callable']);
            $resolver->setNormalizer('on_batch_end', static function (Options $options, $value) {
                return $value ?? static function () {
                    };
            });
        }

        return $resolver->resolve($options);
    }
}
