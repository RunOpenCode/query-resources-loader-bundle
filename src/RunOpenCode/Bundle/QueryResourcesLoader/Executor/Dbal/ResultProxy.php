<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal;

use Doctrine\DBAL\Driver\Exception as DbalDriverException;
use Doctrine\DBAL\Driver\Result as DbalDriverResult;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Result as DbalResult;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\DriverException;

/**
 * A simple proxy class which wraps Doctrine DBAL result
 * and provides a way to catch exceptions and rethrow them
 * as DriverException.
 *
 * It provides serialization of the results as well as
 * portable method for counting the results.
 *
 * @internal
 *
 * @psalm-suppress InternalClass, InternalMethod
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class ResultProxy implements \Countable
{
    public function __construct(
        private DbalResult|DbalDriverResult|ArrayResult $result
    ) {
        // noop.
    }

    /**
     * @throws DriverException|\Doctrine\DBAL\Driver\Exception
     */
    public function fetchOne(): mixed
    {
        try {
            return $this->result->fetchOne();
        } catch (DbalException|DbalDriverException $exception) {
            throw $this->createException($exception, __METHOD__);
        }
    }

    /**
     * @return array<string, mixed>|false
     *
     * @throws DriverException
     */
    public function fetchAssociative(): array|false
    {
        try {
            return $this->result->fetchAssociative();
        } catch (DbalException|DbalDriverException $exception) {
            throw $this->createException($exception, __METHOD__);
        }
    }

    /**
     * @throws DriverException
     */
    public function columnCount(): int
    {
        try {
            return $this->result->columnCount();
        } catch (DbalException|DbalDriverException $exception) {
            throw $this->createException($exception, __METHOD__);
        }
    }

    /**
     * @return list<mixed>|false
     *
     * @throws DriverException
     */
    public function fetchNumeric(): array|false
    {
        try {
            return $this->result->fetchNumeric();
        } catch (DbalException|DbalDriverException $exception) {
            throw $this->createException($exception, __METHOD__);
        }
    }

    /**
     * @return list<list<mixed>>
     *
     * @throws DriverException
     */
    public function fetchAllNumeric(): array
    {
        try {
            return $this->result->fetchAllNumeric();
        } catch (DbalException|DbalDriverException $exception) {
            throw $this->createException($exception, __METHOD__);
        }
    }

    /**
     * @return list<array<string,mixed>>
     *
     * @throws DriverException
     */
    public function fetchAllAssociative(): array
    {
        try {
            return $this->result->fetchAllAssociative();
        } catch (DbalException|DbalDriverException $exception) {
            throw $this->createException($exception, __METHOD__);
        }
    }

    /**
     * @return list<mixed>
     *
     * @throws DriverException
     */
    public function fetchFirstColumn(): array
    {
        try {
            return $this->result->fetchFirstColumn();
        } catch (DbalException|DbalDriverException $exception) {
            throw $this->createException($exception, __METHOD__);
        }
    }

    /**
     * @throws DriverException
     */
    public function rowCount(): int
    {
        try {
            return (int)$this->result->rowCount();
        } catch (DbalException|DbalDriverException $exception) {
            throw $this->createException($exception, __METHOD__);
        }
    }

    public function free(): void
    {
        $this->result->free();
    }

    /**
     * {@inheritdoc}
     *
     * @throws DriverException
     */
    public function count(): int
    {
        if (0 === $this->columnCount()) {
            return $this->rowCount();
        }

        $count = $this->rowCount();

        if ($count > 0) {
            return $count;
        }

        if ($this->result instanceof ArrayResult) {
            return $this->rowCount();
        }

        // If rowCount() returns 0, we need to fetch all rows to count them,
        // and we need to replace result with array result to be able to fetch all rows.
        $rows        = $this->fetchAllNumeric();
        $columnNames = [];

        for ($index = 0; $index < $this->columnCount(); ++$index) {
            $columnNames[] = $this->result->getColumnName($index);
        }

        $this->result = new ArrayResult($columnNames, $rows);

        return $this->rowCount();
    }

    /**
     * @return array{
     *     columnNames: list<string>,
     *     rows: list<list<mixed>>
     * }
     */
    public function __serialize(): array
    {
        $columnNames = [];
        $rows        = $this->result->fetchAllNumeric();

        for ($index = 0; $index < $this->columnCount(); ++$index) {
            $columnNames[] = $this->result->getColumnName($index);
        }

        // Replace result with array result so this resultset can be reused.
        $this->result = new ArrayResult($columnNames, $rows);

        return [
            'columnNames' => $columnNames,
            'rows'        => $rows,
        ];
    }

    /**
     * @param array{
     *     columnNames: list<string>,
     *     rows: list<list<mixed>>
     * } $data
     */
    public function __unserialize(array $data): void
    {
        $this->result = new ArrayResult($data['columnNames'], $data['rows']);
    }

    private function createException(\Throwable $inner, string $method): DriverException
    {
        return new DriverException(\sprintf(
            'An error occurred while executing method "%s::%s()" on underlying Dbal result implementation.',
            $this->result::class,
            $method,
        ), $inner);
    }
}
