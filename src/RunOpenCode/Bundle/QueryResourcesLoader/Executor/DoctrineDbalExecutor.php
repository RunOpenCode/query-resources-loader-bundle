<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor;

use Doctrine\DBAL\TransactionIsolationLevel;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface;
use Doctrine\DBAL\Connection;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\IterateResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\LoaderInterface;
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
        $query  = $this->loader->get($name, $parameters);
        $result = $this->connection->executeQuery($query, $parameters, $types);

        return new DoctrineDbalExecutionResult($result);
    }

    /**
     * {@inheritdoc}
     *
     * @param array{
     *     isolation?: TransactionIsolationLevel::*|null
     * } $options
     */
    public function transactional(\Closure $scope, array $options = []): void
    {
        $options           = $this->resolveOptions($options);
        $previousIsolation = $this->connection->getTransactionIsolation();


        if (null !== $options['isolation']) {
            $this->connection->setTransactionIsolation($options['isolation']);
        }

        $this->connection->beginTransaction();

        try {
            $scope($this);
            $this->connection->commit();
        } catch (\Exception $exception) {
            $this->connection->rollBack();
            throw $exception;
        } finally {
            if (null !== $options['isolation']) {
                $this->connection->setTransactionIsolation($previousIsolation);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function iterate(string $name, array $parameters = [], array $types = [], array $options = []): IterateResultInterface
    {
        return new DoctrineDbalIterateResult($this->connection, $this->loader, $name, $parameters, $types, $options);
    }

    /**
     * @param array{
     *     isolation?: TransactionIsolationLevel::*|null
     * } $options
     *
     * @return array{
     *     isolation: TransactionIsolationLevel::*|null
     * }
     */
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

        return $resolver->resolve($options); // @phpstan-ignore-line
    }
}
