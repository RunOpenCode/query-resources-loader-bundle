<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

/**
 * Represents cache identity for execution result.
 *
 * Class implementing this interface should be immutable.
 */
interface CacheIdentityInterface
{
    /**
     * Get cache key.
     */
    public function getKey(): string;

    /**
     * Get cache tags.
     *
     * If no tags are provided, empty array should be returned.
     *
     * @return string[]
     */
    public function getTags(): array;

    /**
     * Get cache time-to-live.
     *
     * If no time-to-live is provided, NULL should be returned,
     * and that means that cache item should not expire.
     */
    public function getTtl(): ?int;

    /**
     * Add tags to cache identity.
     *
     * This method should return new instance of cache identity
     * with provided tags added to current tags.
     *
     * @param string ...$tags Tags to add to cache identity.
     */
    public function tag(string ...$tags): self;

    /**
     * Set cache key.
     *
     * This method should return new instance of cache identity
     * with provided key.
     *
     * @param string[]|string $key Cache key. If array is provided, it will be imploded with '|' separator.
     */
    public function withKey(array|string $key): self;

    /**
     * Set cache tags.
     *
     * This method should return new instance of cache identity
     * with provided tags. Use NULL or empty array for no tags.
     *
     * @param string[]|null $tags Cache tags.
     */
    public function withTags(?array $tags = null): self;

    /**
     * Set cache time to live.
     *
     * This method should return new instance of cache identity
     * with provided time-to-live.
     *
     * If NULL is provided, that means that cache item should not expire.
     * Value given as integer is number of seconds from now when cache
     * item should expire. When \DateTimeInterface is provided, it denotes
     * exact date and time when cache item should expire.
     *
     * @param \DateTimeInterface|int|null $ttl Time-to-live.
     */
    public function withTtl(\DateTimeInterface|int|null $ttl = null): self;
}
