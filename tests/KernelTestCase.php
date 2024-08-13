<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests;

use Doctrine\Bundle\DoctrineBundle\Middleware\DebugMiddleware;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Middleware;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Resources\App\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as SymfonyKernelTestCase;

/**
 * @psalm-suppress UnnecessaryVarAnnotation, PossiblyNullFunctionCall, UndefinedThisPropertyFetch
 */
abstract class KernelTestCase extends SymfonyKernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected function clearLoggedQueryStatements(): void
    {
        /** @var Configuration $configuration */
        $configuration = $this->getContainer()->get(Connection::class)->getConfiguration(); // @phpstan-ignore-line
        $logger        = \array_values(\array_filter($configuration->getMiddlewares(), static fn(Middleware $middleware): bool => $middleware instanceof DebugMiddleware))[0] ?? null;

        \assert($logger instanceof DebugMiddleware);

        \Closure::bind(function(): void {
            $this->debugDataHolder->reset();
        }, $logger, DebugMiddleware::class)();
    }

    /**
     * @return array<string, string[]>
     */
    protected function getLoggedQueryStatements(): array
    {
        /** @var Configuration $configuration */
        $configuration = $this->getContainer()->get(Connection::class)->getConfiguration(); // @phpstan-ignore-line
        $logger        = \array_values(\array_filter($configuration->getMiddlewares(), static fn(Middleware $middleware): bool => $middleware instanceof DebugMiddleware))[0] ?? null;

        \assert($logger instanceof DebugMiddleware);

        $records = \Closure::bind(function(): array {
            return $this->debugDataHolder->getData();
        }, $logger, DebugMiddleware::class)();

        $result = [];

        foreach ($records as $connection => $logs) {
            foreach ($logs as $log) {
                $result[$connection]   = $result[$connection] ?? [];
                $result[$connection][] = $log['sql'];
            }
        }

        return $result;
    }
}
