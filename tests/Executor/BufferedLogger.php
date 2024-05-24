<?php

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Executor;

use Doctrine\DBAL\Logging\SQLLogger;

final class BufferedLogger implements SQLLogger
{
    private array $records = [];

    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        $this->records[] = [$sql, $params, $types];
    }

    public function stopQuery()
    {
        // noop
    }

    /**
     * @return array
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    public function clear(): void
    {
        $this->records = [];
    }

    public function getLastQuery(): ?string
    {
        return $this->records[\count($this->records) - 1][0] ?? null;
    }

    public function getQueries(): array
    {
        return \array_map(static function($record): string {
            return $record[0];
        }, $this->records);
    }
}
