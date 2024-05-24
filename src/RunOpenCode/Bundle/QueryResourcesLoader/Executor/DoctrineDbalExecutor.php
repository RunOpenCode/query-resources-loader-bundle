<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\TransactionIsolationLevel;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\IterateResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\LoaderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Doctrine Dbal query executor.
 *
 * @psalm-suppress MoreSpecificImplementedParamType
 *                 
 * @phpstan-type IsolationLevel = TransactionIsolationLevel::READ_UNCOMMITTED|TransactionIsolationLevel::READ_COMMITTED|TransactionIsolationLevel::REPEATABLE_READ|TransactionIsolationLevel::SERIALIZABLE
 * @psalm-type IsolationLevel = TransactionIsolationLevel::READ_UNCOMMITTED|TransactionIsolationLevel::READ_COMMITTED|TransactionIsolationLevel::REPEATABLE_READ|TransactionIsolationLevel::SERIALIZABLE
 */
final class DoctrineDbalExecutor implements ExecutorInterface
{
    /**
     * @var Connection
     */
    private Connection $connection;

    private LoaderInterface $loader;

    public function __construct(Connection $connection, LoaderInterface $loader)
    {
        $this->connection = $connection;
        $this->loader     = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $name, array $parameters = [], array $types = []): ExecutionResultInterface
    {
        $query = $this->loader->get($name, $parameters);
        /** @psalm-suppress InvalidArgument */
        $result = $this->connection->executeQuery($query, $parameters, $types); // @phpstan-ignore-line

        return new DoctrineDbalExecutionResult($result);
    }

    /**
     * {@inheritdoc}
     *
     * @param array{
     *     isolation?: IsolationLevel|null
     * } $options
     */
    public function transactional(\Closure $scope, array $options = [])
    {
        $options           = $this->resolveOptions($options);
        $previousIsolation = $this->connection->getTransactionIsolation();


        if (null !== $options['isolation']) {
            $this->connection->setTransactionIsolation($options['isolation']);
        }

        $this->connection->beginTransaction();

        $result = null;

        try {
            $result = $scope($this);
            $this->connection->commit();
        } catch (\Exception $exception) {
            $this->connection->rollBack();
            throw $exception;
        } finally {
            if (null !== $options['isolation']) {
                $this->connection->setTransactionIsolation($previousIsolation);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress RedundantCastGivenDocblockType, InvalidArgument
     */
    public function iterate(string $name, array $parameters = [], array $types = [], array $options = []): IterateResultInterface
    {
        return new DoctrineDbalIterateResult($this->connection, $this->loader, $name, $parameters, $types, $options);
    }

    /**
     * @param array{
     *     isolation?: IsolationLevel|null
     * } $options
     *
     * @return array{
     *     isolation: IsolationLevel|null
     * }
     */
    private function resolveOptions(array $options): array
    {
        /** @var OptionsResolver|null $resolver */
        static $resolver;

        if (null === $resolver) {
            $resolver = new OptionsResolver();

            $resolver->setDefault('isolation', null);
            $resolver->setAllowedTypes('isolation', ['int', 'null', TransactionIsolationLevel::class]);
            $resolver->setAllowedValues('isolation', [
                TransactionIsolationLevel::READ_UNCOMMITTED,
                TransactionIsolationLevel::READ_COMMITTED,
                TransactionIsolationLevel::REPEATABLE_READ,
                TransactionIsolationLevel::SERIALIZABLE,
                null,
            ]);
        }

        return $resolver->resolve($options); // @phpstan-ignore-line
    }
}
