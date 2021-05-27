<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NonUniqueResultException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NoResultException;

/**
 * Doctrine Dbal executor result statement wrapper that provides you with useful methods when fetching results from
 * SELECT statement.
 *
 * @implements \IteratorAggregate<mixed, mixed>
 */
final class DoctrineDbalExecutionResult implements \IteratorAggregate, ExecutionResultInterface, Statement
{
    /**
     * @var Statement<mixed>
     */
    private Statement $statement;

    private bool $debug;

    /**
     * @param Statement<mixed> $statement
     */
    public function __construct(Statement $statement, bool $debug = true)
    {
        $this->statement = $statement;
        $this->debug     = $debug;
    }

    public function getSingleScalarResult()
    {
        $scalar = $this->statement->fetchColumn(0);

        if (false === $scalar) {
            throw new NoResultException('Expected on result for given query.');
        }

        if (false !== $this->statement->fetch()) {
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

        while ($val = $this->statement->fetchColumn()) {
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
        $row = $this->statement->fetch(\PDO::FETCH_BOTH);

        if (false === $row) {
            throw new NoResultException('Expected on result for given query.');
        }

        if (false !== $this->statement->fetch()) {
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
    public function closeCursor(): bool
    {
        return $this->statement->closeCursor();
    }

    /**
     * {@inheritdoc}
     */
    public function columnCount(): int
    {
        return $this->statement->columnCount();
    }

    /**
     * {@inheritdoc}
     */
    public function setFetchMode($fetchMode, $arg2 = null, $arg3 = null): bool
    {
        return $this->statement->setFetchMode($fetchMode, $arg2, $arg3);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($fetchMode = null, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {
        return $this->statement->fetch($fetchMode, $cursorOrientation, $cursorOffset);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($fetchMode = null, $fetchArgument = null, $ctorArgs = null)
    {
        return $this->statement->fetchAll($fetchMode, $fetchArgument, $ctorArgs);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchColumn($columnIndex = 0)
    {
        return $this->statement->fetchColumn($columnIndex);
    }

    /**
     * {@inheritdoc}
     */
    public function bindValue($param, $value, $type = null)
    {
        return $this->statement->bindValue($param, $value, $type ?? ParameterType::STRING);
    }

    /**
     * {@inheritdoc}
     */
    public function bindParam($column, &$variable, $type = null, $length = null)
    {
        return $this->statement->bindParam($column, $variable, $type ?? ParameterType::STRING, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function errorCode()
    {
        return $this->statement->errorCode();
    }

    /**
     * {@inheritdoc}
     */
    public function errorInfo()
    {
        return $this->statement->errorInfo();
    }

    public function execute($params = null)
    {
        return $this->statement->execute($params);
    }

    /**
     * {@inheritdoc}
     */
    public function rowCount()
    {
        return $this->statement->rowCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        while (false !== ($row = $this->statement->fetch(\PDO::FETCH_BOTH))) {
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

        if (0 === $this->statement->columnCount()) {
            return $this->statement->rowCount();
        }

        /** @var iterable<mixed>|array $data */
        $data = $this->fetchAll();

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
        return $this->statement->{$name};
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
        $this->statement->{$name} = $value;
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
        $callable = [$this->statement, $name];

        return call_user_func_array($callable, $arguments);
    }
}
