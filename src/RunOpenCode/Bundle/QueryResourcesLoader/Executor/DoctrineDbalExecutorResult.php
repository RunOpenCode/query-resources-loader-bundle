<?php

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor;

use Doctrine\DBAL\Driver\Statement;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NonUniqueResultException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\NoResultException;

final class DoctrineDbalExecutorResult
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

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->statement, $name), $arguments);
    }
}
