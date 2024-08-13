<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Cache;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheIdentity;
use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheMiddleware;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Executor\ExecutionResultStub;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CacheMiddlewareTest extends TestCase
{
    public function testItCachesResultSet(): void
    {
        $invocations = 0;
        $middleware  = new CacheMiddleware();
        $callable    = static function() use (&$invocations): ExecutionResultInterface {
            $invocations++;
            return new ExecutionResultStub();
        };
        $options     = Options::create([
            'cache' => CacheIdentity::create('foo'),
        ]);

        $middleware->__invoke('query', Parameters::create(), $options, $callable);
        $middleware->__invoke('query', Parameters::create(), $options, $callable);

        $this->assertSame(1, $invocations);
    }

    public function testItDoesNotCachesResultSet(): void
    {
        $invocations = 0;
        $middleware  = new CacheMiddleware();
        $callable    = static function() use (&$invocations): ExecutionResultInterface {
            $invocations++;
            return new ExecutionResultStub();
        };

        $middleware->__invoke('query', Parameters::create(), Options::create(), $callable);
        $middleware->__invoke('query', Parameters::create(), Options::create(), $callable);

        $this->assertSame(2, $invocations);
    }

    public function testItSetsProperCacheMetadata(): void
    {
        $cache  = $this->createMock(CacheInterface::class);
        $item   = $this->createMock(ItemInterface::class);
        $save   = false;
        $result = new ExecutionResultStub();

        $item
            ->expects($this->once())
            ->method('set')
            ->with($result);

        $item
            ->expects($this->once())
            ->method('tag')
            ->with(['tag1', 'tag2']);

        $item
            ->expects($this->once())
            ->method('expiresAfter')
            ->with(3600);

        $cache
            ->expects($this->once())
            ->method('get')
            ->with('foo', $this->callback(static function(callable $callback) use ($item, &$save): bool {
                $callback($item, $save);
                return true;
            }))
            ->willReturn(new ExecutionResultStub());

        $middleware = new CacheMiddleware($cache);
        $callable   = static fn(): ExecutionResultInterface => $result;

        $middleware->__invoke('query', Parameters::create(), Options::create([
            'cache' => CacheIdentity::create('foo', ['tag1', 'tag2'], 3600),
        ]), $callable);

        $this->assertTrue($save); // @phpstan-ignore-line
    }
}
