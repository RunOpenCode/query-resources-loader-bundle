<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Cache;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheIdentity;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentityInterface;

final class CacheIdentityTest extends TestCase
{
    private CacheIdentityInterface $identity;

    protected function setUp(): void
    {
        parent::setUp();
        $this->identity = new CacheIdentity('key', ['foo'], 3600);
    }

    public function testCreate(): void
    {
        $identity = CacheIdentity::create('key', ['foo'], 3600);

        $this->assertEquals('key', $identity->getKey());
        $this->assertEquals(['foo'], $identity->getTags());
        $this->assertEquals(3600, $identity->getTtl());
    }

    public function testTag(): void
    {
        $identity = $this->identity->tag('bar', 'baz');

        $this->assertEquals(['foo', 'bar', 'baz'], $identity->getTags());
        $this->assertNotEquals($this->identity, $identity);
    }

    public function testWithKey(): void
    {
        $identity = $this->identity->withKey('foo');

        $this->assertEquals('foo', $identity->getKey());
        $this->assertNotEquals($this->identity, $identity);
    }

    public function testWithTags(): void
    {
        $identity = $this->identity->withTags(['bar', 'baz']);

        $this->assertEquals(['bar', 'baz'], $identity->getTags());
        $this->assertNotEquals($this->identity, $identity);
    }

    public function testWithTtl(): void
    {
        $identity = $this->identity->withTtl(7200);

        $this->assertEquals(7200, $identity->getTtl());
        $this->assertNotEquals($this->identity, $identity);
    }
}
