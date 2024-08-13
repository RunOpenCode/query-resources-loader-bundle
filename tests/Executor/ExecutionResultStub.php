<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Executor;

use Doctrine\DBAL\Cache\ArrayResult;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DoctrineDbalExecutionResult;

/**
 * @implements \IteratorAggregate<mixed>
 *
 * @psalm-suppress InternalClass, InternalMethod, ArgumentTypeCoercion
 */
final class ExecutionResultStub implements ExecutionResultInterface, \IteratorAggregate
{
    private DoctrineDbalExecutionResult $result;

    /**
     * @param array<int, array<string, mixed>> $data
     */
    public function __construct(array $data = [])
    {
        $this->result = new DoctrineDbalExecutionResult(new ArrayResult($data));
    }

    public function getSingleScalarResult(): mixed
    {
        return $this->result->getSingleScalarResult();
    }

    public function getSingleScalarResultOrDefault(mixed $default): mixed
    {
        return $this->result->getSingleScalarResultOrDefault($default);
    }

    public function getSingleScalarResultOrNull(): mixed
    {
        return $this->result->getSingleScalarResultOrNull();
    }

    public function getScalarResult(): array
    {
        return $this->result->getScalarResult();
    }

    public function getScalarResultOrDefault(mixed $default): mixed
    {
        return $this->result->getScalarResultOrDefault($default);
    }

    public function getScalarResultOrNull(): mixed
    {
        return $this->result->getScalarResultOrNull();
    }

    public function getSingleResult(): array
    {
        return $this->result->getSingleResult();
    }

    public function getSingleResultOrDefault(mixed $default): mixed
    {
        return $this->result->getSingleResultOrDefault($default);
    }

    public function getSingleResultOrNull(): mixed
    {
        return $this->result->getSingleResultOrNull();
    }

    public function count(): int
    {
        return $this->result->count();
    }

    public function getIterator(): \Traversable
    {
        return $this->result->getIterator();
    }
}
