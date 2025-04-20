<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests;

use Doctrine\Bundle\DoctrineBundle\Middleware\DebugMiddleware;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Middleware;
use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheMiddleware;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Fixtures\Fixtures;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Resources\App\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as SymfonyKernelTestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @psalm-suppress UnnecessaryVarAnnotation, PossiblyNullFunctionCall, UndefinedThisPropertyFetch
 */
abstract class KernelTestCase extends SymfonyKernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->clearCache();
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    final protected function createFixtures(): void
    {
        $this->getContainer()->get(Fixtures::class)->execute(); // @phpstan-ignore-line
        $this->clearLoggedQueryStatements();
    }

    final protected function clearLoggedQueryStatements(): void
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
    final protected function getLoggedQueryStatements(): array
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

    /**
     * Clear cache for the current test.
     */
    final protected function clearCache(): void
    {
        $booted = self::$booted;

        if (!$booted) {
            self::bootKernel();
        }

        /** @var CacheInterface&TagAwareCacheInterface $pool */
        $pool = $this->getContainer()->get('app.roc_test_cache');

        $pool->invalidateTags([
            CacheMiddleware::TAG,
        ]);

        if ($booted) {
            self::ensureKernelShutdown();
        }
    }
}
