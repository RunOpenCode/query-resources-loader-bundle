<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Executor;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryExecutorInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\ExecutorsRegistry;

final class ExecutorsRegistryTest extends TestCase
{
    public function testItGetsExecutor(): void
    {
        $registry = new ExecutorsRegistry([
            'default' => $this->createMock(QueryExecutorInterface::class),
        ]);

        $this->assertInstanceOf(QueryExecutorInterface::class, $registry->get());
        $this->assertInstanceOf(QueryExecutorInterface::class, $registry->get('default'));
    }

    public function testItThrowsExceptionWhenExecutorDoesNotExists(): void
    {
        $this->expectException(RuntimeException::class);

        $registry = new ExecutorsRegistry([
            'default' => $this->createMock(QueryExecutorInterface::class),
        ]);

        $registry->get('foo');
    }
}
