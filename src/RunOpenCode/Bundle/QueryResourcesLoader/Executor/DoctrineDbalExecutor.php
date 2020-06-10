<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Statement;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface;
use Doctrine\DBAL\Connection;

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
    public function execute(string $query, array $parameters = [], array $types = []): ExecutionResultInterface
    {
        /** @var Statement<mixed> $statement */
        $statement = $this->connection->executeQuery($query, $parameters, $types);

        return new DoctrineDbalExecutionResult($statement);
    }
}
