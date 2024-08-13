<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Cache;

/**
 * Cache identity for execution result.
 *
 * Cache identity identifies cache item for execution result
 * within caching middleware.
 *
 * For custom cache identity, implement builder pattern.
 */
final readonly class CacheIdentity
{
    /**
     * Cache key.
     */
    public string $key;

    /**
     * Cache tags.
     *
     * @var string[]
     */
    public array $tags;

    /**
     * Cache time-to-live. NULL if cache item should not expire.
     */
    public int|null $ttl;

    /**
     * @param string[] $tags
     */
    public function __construct(
        string                      $key,
        ?array                      $tags = null,
        \DateTimeInterface|int|null $ttl = null,
    ) {
        $this->key  = $key;
        $this->tags = $tags ?? [];
        $this->ttl  = $ttl instanceof \DateTimeInterface ? $ttl->getTimestamp() - \time() : $ttl;
    }

    /**
     * @param string[]|null $tags Tags to use for cache item instead of current tags.
     *
     * @return self
     */
    public static function create(
        string                      $key,
        ?array                      $tags = null,
        \DateTimeInterface|int|null $ttl = null,
    ): self {
        return new self($key, $tags, $ttl);
    }

    public function tag(string ...$tags): self
    {
        return new self($this->key, \array_unique(\array_merge($this->tags, $tags)), $this->ttl);
    }

    public function withKey(string $key): self
    {
        return new self($key, $this->tags, $this->ttl);
    }

    /**
     * @param string[]|null $tags Tags to use for cache item instead of current tags.
     */
    public function withTags(?array $tags = null): self
    {
        return new self($this->key, $tags, $this->ttl);
    }

    public function withTtl(\DateTimeInterface|int|null $ttl = null): self
    {
        return new self($this->key, $this->tags, $ttl);
    }
}
