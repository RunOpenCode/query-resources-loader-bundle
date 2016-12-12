<?php

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor;

use Doctrine\DBAL\Driver\Statement;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NonUniqueResultException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NoResultException;

final class DoctrineDbalExecutorResult implements Statement
{
    private $statement;

    public function __construct(Statement $statement)
    {
        $this->statement = $statement;
    }

    public function getSingleScalarResult()
    {
        $scalar = $this->statement->fetchColumn(0);

        if (false === $scalar) {
            throw new NoResultException('Expected on result for given query.');
        }

        if (false !== $this->statement->fetch()) {
            throw new NonUniqueResultException('Expected only ine result for given query.');
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

        if (count($result) < 0) {
            return $default;
        }

        return $result;
    }

    public function getScalarResultOrNull()
    {
        return $this->getScalarResultOrDefault(null);
    }

    public function getSingleRowResult()
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

    public function getSingleRowOrDefault($default)
    {
        try {
            return $this->getSingleRowResult();
        } catch (NoResultException $e) {
            return $default;
        }
    }

    public function getSingleRowOrNull()
    {
        $this->getSingleRowOrDefault(null);
    }

    /**
     * {@inheritdoc}
     */
    public function closeCursor()
    {
        return $this->statement->closeCursor();
    }

    /**
     * {@inheritdoc}
     */
    public function columnCount()
    {
        return $this->statement->columnCount();
    }

    /**
     * {@inheritdoc}
     */
    public function setFetchMode($fetchMode, $arg2 = null, $arg3 = null)
    {
        return $this->statement->setFetchMode($fetchMode, $arg2, $arg3);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($fetchMode = null)
    {
        return $this->statement->fetch($fetchMode);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($fetchMode = null)
    {
        return $this->statement->fetchAll($fetchMode);
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
        return $this->statement->bindValue($param, $value, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function bindParam($column, &$variable, $type = null, $length = null)
    {
        return $this->statement->bindParam($column, $variable, $type, $length);
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

    /**
     * {@inheritdoc}
     */
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
    public function __get($name)
    {
        return $this->statement->{$name};
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        $this->statement->{$name} = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        return isset($this->statement[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->statement, $name), $arguments);
    }
}
