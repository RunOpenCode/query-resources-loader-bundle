<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Cache;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheIdentity;
use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheMiddleware;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentifiableInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentityInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Executor\ExecutionResultStub;

final class HasCacheIdentityTest extends TestCase
{
    public function testItCachesResultSetUsingCacheIdentifiable(): void
    {
        $invocations = 0;
        $middleware  = new CacheMiddleware();
        $callable    = static function() use (&$invocations): ExecutionResultInterface {
            $invocations++;
            return new ExecutionResultStub();
        };
        $options     = Options::create([
            'cache' => new Criteria('foo'),
        ]);

        $middleware->__invoke('query', Parameters::create(), $options, $callable);
        $middleware->__invoke('query', Parameters::create(), $options, $callable);

        $this->assertSame(1, $invocations);
    }
}

final readonly class Criteria implements CacheIdentifiableInterface
{
    public function __construct(public string $title)
    {
        // noop
    }

    public function getCacheIdentity(): CacheIdentityInterface
    {
        return CacheIdentity::create($this->title);
    }
}
