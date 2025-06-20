<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal;

use Doctrine\DBAL\Driver\FetchUtils;
use Doctrine\DBAL\Driver\Result as DbalDriverResult;
use Doctrine\DBAL\Exception\InvalidColumnIndex;
use Doctrine\DBAL\Result as DbalResult;

/**
 * @psalm-suppress InternalClass, InternalMethod
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 *
 * @internal
 */
final class ArrayResult implements DbalDriverResult
{
    private int $num = 0;

    /**
     * @param list<string>      $columnNames The names of the result columns. Must be non-empty.
     * @param list<list<mixed>> $rows        The rows of the result. Each row must have the same number of columns
     *                                       as the number of column names.
     */
    public function __construct(
        private readonly array $columnNames,
        private array          $rows,
    ) {
    }

    public static function create(DbalDriverResult|DbalResult $result): self
    {
        // A new implementation of Dbal, let's leverage it.
        if (\method_exists($result, 'getColumnName')) {
            $rows        = $result->fetchAllNumeric();
            $columnNames = \array_map(fn(int $index): string => $result->getColumnName($index), \range(0, $result->columnCount() - 1));

            return new self($columnNames, $rows);
        }

        // A legacy implementation of Dbal, let's use the old way.
        $data = $result->fetchAllAssociative();

        if (0 === \count($data)) {
            return new self([], []);
        }

        $columnNames = \array_keys($data[0]);
        $rows        = \array_map(fn(array $row): array => \array_values($row), $data);

        return new self($columnNames, $rows);
    }

    public function fetchNumeric(): array|false
    {
        return $this->fetch();
    }

    public function fetchAssociative(): array|false
    {
        $row = $this->fetch();

        if ($row === false) {
            return false;
        }

        return \array_combine($this->columnNames, $row);
    }

    public function fetchOne(): mixed
    {
        $row = $this->fetch();

        if ($row === false) {
            return false;
        }

        return $row[0];
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAllNumeric(): array
    {
        return FetchUtils::fetchAllNumeric($this);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAllAssociative(): array
    {
        return FetchUtils::fetchAllAssociative($this);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchFirstColumn(): array
    {
        return FetchUtils::fetchFirstColumn($this);
    }

    public function rowCount(): int
    {
        return \count($this->rows);
    }

    public function columnCount(): int
    {
        return \count($this->columnNames);
    }

    public function getColumnName(int $index): string
    {
        return $this->columnNames[$index] ?? throw InvalidColumnIndex::new($index);
    }

    public function free(): void
    {
        $this->rows = [];
    }

    /** @return array{list<string>, list<list<mixed>>} */
    public function __serialize(): array
    {
        return [$this->columnNames, $this->rows];
    }

    /** @param array{list<string>, list<list<mixed>>} $data */
    public function __unserialize(array $data): void
    {
        [$this->columnNames, $this->rows] = $data;
    }

    /** @return list<mixed>|false */
    private function fetch(): array|false
    {
        if (!isset($this->rows[$this->num])) {
            return false;
        }

        return $this->rows[$this->num++];
    }
}
