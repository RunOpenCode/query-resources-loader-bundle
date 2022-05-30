<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\TransactionIsolationLevel;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface;
use Doctrine\DBAL\Connection;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\IterateResultInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Doctrine Dbal query executor.
 *
 * @psalm-suppress MoreSpecificImplementedParamType
 */
final class DoctrineDbalExecutor implements ExecutorInterface
{
    /**
     * @var Connection
     */
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     *
     * @param array{
     *     transactional?: bool,
     *     isolation?: TransactionIsolationLevel::*|null
     * } $options
     *
     * @throws Exception
     */
    public function execute(string $query, array $parameters = [], array $types = [], array $options = []): ExecutionResultInterface
    {
        $options = $this->resolveOptions($options);

        if (!$options['transactional']) {
            $result = $this->connection->executeQuery($query, $parameters, $types);
            return new DoctrineDbalExecutionResult($result);
        }

        $currentIsolationLevel = $this->connection->getTransactionIsolation();

        $this->connection->beginTransaction();

        if (null !== $options['isolation']) {
            $this->connection->setTransactionIsolation($options['isolation']);
        }

        try {
            $result = $this->connection->executeQuery($query, $parameters, $types);
            $result = new DoctrineDbalExecutionResult($result);

            $this->connection->commit();

            return $result;
        } catch (\Exception $exception) {
            $this->connection->rollBack();

            throw $exception;
        } finally {
            if (null !== $options['isolation']) {
                $this->connection->setTransactionIsolation($currentIsolationLevel);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function iterate(string $query, array $parameters = [], array $types = [], array $options = []): IterateResultInterface
    {
        return new DoctrineDbalIterateResult($this, $query, $parameters, $types, $options);
    }

    /**
     * @param array{
     *     transactional?: bool,
     *     isolation?: TransactionIsolationLevel::*|null
     * } $options
     *
     * @return array{
     *     transactional: bool,
     *     isolation: TransactionIsolationLevel::*|null
     * }
     */
    private function resolveOptions(array $options): array
    {
        /** @var OptionsResolver|null $resolver */
        static $resolver;

        if (null === $resolver) {
            $resolver = new OptionsResolver();

            $resolver->setDefault('transactional', false);
            $resolver->setAllowedTypes('transactional', 'bool');

            $resolver->setDefault('isolation', null);
            $resolver->setAllowedTypes('isolation', ['int', 'null']);
            $resolver->setAllowedValues('isolation', [
                TransactionIsolationLevel::READ_UNCOMMITTED,
                TransactionIsolationLevel::READ_COMMITTED,
                TransactionIsolationLevel::REPEATABLE_READ,
                TransactionIsolationLevel::SERIALIZABLE,
                null,
            ]);

            /** @psalm-suppress MissingClosureParamType */
            $resolver->setNormalizer('transactional', static function (Options $options, $value): bool {
                if (null !== $options['isolation']) {
                    return true;
                }

                return $value;
            });
        }

        return $resolver->resolve($options); // @phpstan-ignore-line
    }
}
