<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor;

use Doctrine\DBAL\Result as ExecutionResult;
use Doctrine\DBAL\Driver\Result;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NonUniqueResultException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NoResultException;

/**
 * Doctrine Dbal executor result statement wrapper that provides you with useful methods when fetching results from
 * SELECT statement.
 *
 * @implements \IteratorAggregate<mixed, mixed>
 *
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class DoctrineDbalExecutionResult implements \IteratorAggregate, ExecutionResultInterface, Result
{
    private ExecutionResult $result;

    private bool $debug;

    public function __construct(ExecutionResult $result, bool $debug = true)
    {
        $this->result = $result;
        $this->debug  = $debug;
    }

    public function getSingleScalarResult()
    {
        $scalar = $this->result->fetchOne();

        if (false === $scalar) {
            throw new NoResultException('Expected on result for given query.');
        }

        if (false !== $this->result->fetchOne()) {
            throw new NonUniqueResultException('Expected only one result for given query.');
        }

        return $scalar;
    }

    public function getSingleScalarResultOrDefault($default)
    {
        try {
            return $this->getSingleScalarResult();
        } catch (NoResultException $e) {
            return $default;
        }
    }

    public function getSingleScalarResultOrNull()
    {
        return $this->getSingleScalarResultOrDefault(null);
    }

    public function getScalarResult()
    {
        $result = [];

        while ($val = $this->result->fetchOne()) {
            $result[] = $val;
        }

        return $result;
    }

    public function getScalarResultOrDefault($default)
    {
        $result = $this->getScalarResult();

        if (0 === count($result)) {
            return $default;
        }

        return $result;
    }

    public function getScalarResultOrNull()
    {
        return $this->getScalarResultOrDefault(null);
    }

    public function getSingleResult()
    {
        $row = $this->result->fetchAssociative();

        if (false === $row) {
            throw new NoResultException('Expected on result for given query.');
        }

        if (false !== $this->result->fetchAssociative()) {
            throw new NonUniqueResultException('Expected only ine result for given query.');
        }

        return $row;
    }

    public function getSingleResultOrDefault($default)
    {
        try {
            return $this->getSingleResult();
        } catch (NoResultException $e) {
            return $default;
        }
    }

    public function getSingleResultOrNull()
    {
        return $this->getSingleResultOrDefault(null);
    }

    /**
     * {@inheritdoc}
     */
    public function columnCount(): int
    {
        return $this->result->columnCount();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAssociative()
    {
        return $this->result->fetchAssociative();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchNumeric()
    {
        return $this->result->fetchNumeric();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchOne()
    {
        return $this->result->fetchOne();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAllNumeric(): array
    {
        return $this->result->fetchAllNumeric();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAllAssociative(): array
    {
        return $this->result->fetchAllAssociative();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchFirstColumn(): array
    {
        return $this->result->fetchFirstColumn();
    }

    /**
     * {@inheritdoc}
     */
    public function free(): void
    {
        $this->result->free();
    }

    /**
     * {@inheritdoc}
     */
    public function rowCount(): int
    {
        return $this->result->rowCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        while (false !== ($row = $this->result->fetchAssociative())) {
            yield $row;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        if ($this->debug) {
            \trigger_error(
                'It is not advisable to rely on \Countable interface of DoctrineDbalExecutionResult because results depends on underlying DBMS implementation.',
                E_USER_NOTICE
            );
        }

        if (0 === $this->result->columnCount()) {
            return $this->result->rowCount();
        }

        /** @var iterable<mixed>|array $data */
        $data = $this->result->fetchAllAssociative();

        return \count(\is_array($data) ? $data : \iterator_to_array($data));
    }

    /**
     * Proxy to public properties
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->result->{$name};
    }

    /**
     * Proxy to public properties
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->result->{$name} = $value;
    }

    /**
     * Proxy to public properties
     *
     * @param string $name
     *
     * @throws \BadMethodCallException
     */
    public function __isset($name)
    {
        throw new \BadMethodCallException(\sprintf(
            'Method %s on class %s should not be invoked',
            __METHOD__,
            __CLASS__
        ));
    }

    /**
     * Proxy to public methods.
     *
     * @param string       $name
     * @param array<mixed> $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        /** @var callable $callable */
        $callable = [$this->result, $name];

        return call_user_func_array($callable, $arguments);
    }
}
