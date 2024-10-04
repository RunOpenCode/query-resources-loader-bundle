<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Cache;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentityInterface;

/**
 * Cache identity for execution result.
 *
 * Cache identity identifies cache item for execution result
 * within caching middleware.
 *
 * For custom cache identity, implement builder pattern, or
 * use \RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheIdentityTrait.
 */
final readonly class CacheIdentity implements CacheIdentityInterface
{
    /**
     * Cache key.
     */
    private string $key;

    /**
     * Cache tags.
     *
     * @var string[]
     */
    private array $tags;

    /**
     * Cache time-to-live. NULL if cache item should not expire.
     */
    private int|null $ttl;

    /**
     * @param string[]|string $key  Cache key. If array is provided, it will be imploded with '|' separator.
     * @param string[]|null   $tags Cache tags.
     *
     * @psalm-suppress RiskyTruthyFalsyComparison
     */
    public function __construct(
        array|string                $key,
        ?array                      $tags = null,
        \DateTimeInterface|int|null $ttl = null,
    ) {
        $this->key  = \is_array($key) ? \implode('|', $key) : $key;
        $this->tags = empty($tags) ? [] : $tags;
        $this->ttl  = $ttl instanceof \DateTimeInterface ? $ttl->getTimestamp() - \time() : $ttl;
    }

    /**
     * Create new instance.
     *
     * @param string[]|string $key  Cache key. If array is provided, it will be imploded with '|' separator.
     * @param string[]|null   $tags Cache tags.
     */
    public static function create(
        array|string                $key,
        ?array                      $tags = null,
        \DateTimeInterface|int|null $ttl = null,
    ): self {
        return new self($key, $tags, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function tag(string ...$tags): self
    {
        return new self($this->key, \array_unique(\array_merge($this->tags, $tags)), $this->ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function withKey(array|string $key): self
    {
        return new self($key, $this->tags, $this->ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function withTags(?array $tags = null): self
    {
        return new self($this->key, $tags, $this->ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function withTtl(\DateTimeInterface|int|null $ttl = null): self
    {
        return new self($this->key, $this->tags, $ttl);
    }
}
