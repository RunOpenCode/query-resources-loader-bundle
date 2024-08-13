<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\TransactionIsolationLevel;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryExecutorInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExecutionException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\TransactionException;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;

/**
 * Doctrine Dbal query executor.
 *
 * @internal
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class DoctrineDbalQueryExecutor implements QueryExecutorInterface
{
    private readonly Connection $connection;

    /**
     * Connection label.
     */
    private readonly string $label;

    /**
     * @var array<int, TransactionIsolationLevel|int>
     */
    private array $transactionLevelState = [];

    public function __construct(Connection $connection, string $label)
    {
        $this->connection = $connection;
        $this->label      = $label;
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(Options $options): void
    {
        $options   = DbalOptions::from($options);
        $isolation = $this->getTransactionIsolation();

        if (null !== $options->isolation && $options->isolation !== $isolation) {
            $this->transactionLevelState[$this->connection->getTransactionNestingLevel()] = $isolation;
            $this->setTransactionIsolation($options->isolation);
        }

        try {
            $this->connection->beginTransaction();
        } catch (DbalException $exception) {
            throw new TransactionException(\sprintf(
                'Failed to begin transaction using "%s" connection.',
                $this->label,
            ), $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $query, Parameters $parameters, Options $options): ExecutionResultInterface
    {
        $options    = DbalOptions::from($options);
        $parameters = DbalParameters::from($parameters);
        $isolation  = $options->isolation;
        $isolate    = null !== $isolation && $isolation !== $this->getTransactionIsolation();

        if ($isolate) {
            return $this->executeTransactionalQuery($query, $parameters, $options);
        }

        try {
            return new DoctrineDbalExecutionResult(
                $this->connection->executeQuery(
                    $query,
                    $parameters->getParameters(),
                    $parameters->getTypes()
                )
            );
        } catch (\Exception $exception) {
            throw new ExecutionException(\sprintf(
                'Execution of query failed ("%s").',
                $query
            ), $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): void
    {
        try {
            $this->connection->commit();
        } catch (DbalException $exception) {
            throw new TransactionException(\sprintf(
                'Failed to commit transaction using "%s" connection.',
                $this->label,
            ), $exception);
        }

        $this->restoreIsolationLevel();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback(): void
    {
        try {
            $this->connection->rollBack();
        } catch (DbalException $exception) {
            throw new TransactionException(\sprintf(
                'Failed to rollback transaction using "%s" connection.',
                $this->label,
            ), $exception);
        }

        $this->restoreIsolationLevel();
    }

    /**
     * Execute query in isolated transaction.
     *
     * @param string         $query      Query to execute.
     * @param DbalParameters $parameters Parameters for query.
     * @param DbalOptions    $options    Executor specific options.
     *
     * @return DoctrineDbalExecutionResult Query result.
     *
     * @throws ExecutionException If execution fails. Wraps low level exception.
     * @throws RuntimeException If unknown error occurred.
     */
    private function executeTransactionalQuery(string $query, DbalParameters $parameters, DbalOptions $options): DoctrineDbalExecutionResult
    {
        $this->beginTransaction($options);

        try {
            $result = $this->connection->executeQuery(
                $query,
                $parameters->getParameters(),
                $parameters->getTypes()
            );

            $this->commit();

            return new DoctrineDbalExecutionResult($result);
        } catch (\Exception $exception) {
            $this->rollback();

            \assert(null !== $options->isolation);

            throw new ExecutionException(\sprintf(
                'Execution of query "%s" failed within transactional scope using "%s" connection and isolation level "%s".',
                $query,
                $this->label,
                $this->getIsolationLevelName($options->isolation),
            ), $exception);
        }
    }

    /**
     * Get current transaction isolation level.
     *
     * @return TransactionIsolationLevel|int Current transaction isolation level.
     *
     * @throws RuntimeException If failed to get transaction isolation level.
     *
     * @phpstan-ignore-next-line
     */
    private function getTransactionIsolation(): TransactionIsolationLevel|int
    {
        try {
            return $this->connection->getTransactionIsolation();
        } catch (DbalException $exception) {
            throw new RuntimeException(\sprintf(
                'Failed to get transaction isolation level using "%s" connection.',
                $this->label,
            ), $exception);
        }
    }

    /**
     * Set transaction isolation level.
     *
     * @param TransactionIsolationLevel|int $level Transaction isolation level.
     *
     * @throws RuntimeException If failed to set transaction isolation level.
     */
    private function setTransactionIsolation(TransactionIsolationLevel|int $level): void
    {
        try {
            /**
             * @psalm-suppress PossiblyInvalidArgument
             * @phpstan-ignore-next-line
             */
            $this->connection->setTransactionIsolation($level);
        } catch (DbalException $exception) {
            throw new RuntimeException(\sprintf(
                'Failed to set transaction isolation level "%s" using "%s" connection.',
                $this->getIsolationLevelName($level),
                $this->label,
            ), $exception);
        }
    }

    /**
     * Restore isolation level.
     *
     * @throws RuntimeException If failed to restore isolation level.
     */
    private function restoreIsolationLevel(): void
    {
        $transactionNestingLevel = $this->connection->getTransactionNestingLevel();
        $previousIsolation       = $this->transactionLevelState[$transactionNestingLevel] ?? null;

        if (null === $previousIsolation) {
            return;
        }

        $this->setTransactionIsolation($previousIsolation);

        unset($this->transactionLevelState[$transactionNestingLevel]);
    }

    private function getIsolationLevelName(TransactionIsolationLevel|int $level): string
    {
        return match ($level) {
            TransactionIsolationLevel::READ_UNCOMMITTED => 'READ UNCOMMITTED',
            TransactionIsolationLevel::READ_COMMITTED => 'READ COMMITTED',
            TransactionIsolationLevel::REPEATABLE_READ => 'REPEATABLE READ',
            TransactionIsolationLevel::SERIALIZABLE => 'SERIALIZABLE',
            default => throw new \InvalidArgumentException(\sprintf(
                'Unknown transaction isolation level "%s".',
                $level,
            )),
        };
    }
}
