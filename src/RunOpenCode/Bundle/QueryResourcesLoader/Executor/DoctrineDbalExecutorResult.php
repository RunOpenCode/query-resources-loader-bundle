<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor;

use Doctrine\DBAL\Driver\Statement;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NonUniqueResultException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NoResultException;

/**
 * Class DoctrineDbalExecutorResult
 *
 * Doctrine Dbal executor result statement wrapper that provides you with useful methods when fetching results from
 * SELECT statement.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\Executor
 */
final class DoctrineDbalExecutorResult implements \IteratorAggregate, Statement
{
    /**
     * @var Statement
     */
    private $statement;

    /**
     * DoctrineDbalExecutorResult constructor.
     *
     * @param Statement $statement Wrapped statement.
     */
    public function __construct(Statement $statement)
    {
        $this->statement = $statement;
    }

    /**
     * Get single scalar result.
     *
     * @return mixed A single scalar value.
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
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

    /**
     * Get single scalar result or default value if there are no results of executed
     * SELECT statement.
     *
     * @param mixed $default A default single scalar value.
     * @return mixed A single scalar value.
     */
    public function getSingleScalarResultOrDefault($default)
    {
        try {
            return $this->getSingleScalarResult();
        } catch (NoResultException $e) {
            return $default;
        }
    }

    /**
     * Get single scalar result or NULL value if there are no results of executed
     * SELECT statement.
     *
     * @return mixed|null A single scalar value.
     */
    public function getSingleScalarResultOrNull()
    {
        return $this->getSingleScalarResultOrDefault(null);
    }

    /**
     * Get collection of scalar values.
     *
     * @return array A collection of scalar values.
     */
    public function getScalarResult()
    {
        $result = [];

        while ($val = $this->statement->fetchColumn()) {
            $result[] = $val;
        }

        return $result;
    }

    /**
     * Get collection of scalar vales, or default value if collection is empty.
     *
     * @param mixed $default A default value.
     * @return array|mixed A collection of scalar values or default value.
     */
    public function getScalarResultOrDefault($default)
    {
        $result = $this->getScalarResult();

        if (0 === count($result)) {
            return $default;
        }

        return $result;
    }

    /**
     * Get collection of scalar vales, or NULL value if collection is empty.
     *
     * @return array|mixed A collection of NULL value.
     */
    public function getScalarResultOrNull()
    {
        return $this->getScalarResultOrDefault(null);
    }

    /**
     * Get single (first) row result from result set.
     *
     * @return array A single (first) row of result set.
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
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

    /**
     * Get single (first) row result from result set or default value if result set is empty.
     *
     * @param mixed $default Default value if result set is empty.
     * @return array|mixed A single (first) row of result set.
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getSingleRowOrDefault($default)
    {
        try {
            return $this->getSingleRowResult();
        } catch (NoResultException $e) {
            return $default;
        }
    }

    /**
     * Get single (first) row result from result set or NULL value if result set is empty.
     *
     * @return array A single (first) row of result set.
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
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

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        while (false !== ($row = $this->statement->fetch(\PDO::FETCH_BOTH))) {
            yield $row;
        }
    }
}
