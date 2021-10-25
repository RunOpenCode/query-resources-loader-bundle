<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\TransactionIsolationLevel;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface;
use Doctrine\DBAL\Connection;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\IterateResultInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Doctrine Dbal query executor.
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
     * @throws DBALException
     */
    public function execute(string $query, array $parameters = [], array $types = [], array $options = []): ExecutionResultInterface
    {
        $options               = $this->resolveOptions($options);
        $currentIsolationLevel = $this->connection->getTransactionIsolation();

        if (null !== $options['isolation']) {
            $this->connection->setTransactionIsolation($options['isolation']);
        }

        /** @var Statement<mixed> $statement */
        $statement = $this->connection->executeQuery($query, $parameters, $types);
        $result    = new DoctrineDbalExecutionResult($statement);

        if (null !== $options['isolation']) {
            $this->connection->setTransactionIsolation($currentIsolationLevel);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DBALException
     */
    public function iterate(string $query, array $parameters = [], array $types = [], array $options = []): IterateResultInterface
    {
        return new DoctrineDbalIterateResult($this, $query, $parameters, $types, $options);
    }

    private function resolveOptions(array $options): array
    {
        /** @var OptionsResolver|null $resolver */
        static $resolver;

        if (null === $resolver) {
            $resolver = new OptionsResolver();

            $resolver->setDefault('isolation', null);
            $resolver->setAllowedTypes('isolation', ['int', 'null']);
            $resolver->setAllowedValues('isolation', [
                TransactionIsolationLevel::READ_UNCOMMITTED,
                TransactionIsolationLevel::READ_COMMITTED,
                TransactionIsolationLevel::REPEATABLE_READ,
                TransactionIsolationLevel::SERIALIZABLE,
                null,
            ]);
        }

        return $resolver->resolve($options);
    }
}
