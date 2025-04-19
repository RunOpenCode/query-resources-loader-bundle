<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Cache;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentifiableInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentityInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultAwareInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * Cache identity for execution result.
 *
 * Cache identity identifies cache item for execution result
 * within caching middleware.
 *
 * For custom cache identity, you may implement
 * \RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentityInterface.
 *
 * For integration with your own criteria value objects, consult
 * \RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentifiableInterface
 *
 * @see CacheIdentityInterface
 * @see CacheIdentifiableInterface
 *
 * Do note that cache key and tags are sanitized, so you can use any character
 * but make sure that you are aware of that and when invalidating cache items
 * you use sanitized values.
 *
 * @phpstan-type TagsExtractorFn \Closure(ExecutionResultInterface): string[] | null
 * @phpstan-type TtlExtractorFn \Closure(ExecutionResultInterface): int | null
 *
 * @implements ExecutionResultAwareInterface<CacheIdentity>
 */
final readonly class CacheIdentity implements CacheIdentityInterface, ExecutionResultAwareInterface
{
    private string $key;

    /**
     * @var string[]
     */
    private array $tags;

    private int|null $ttl;

    /**
     * @var TagsExtractorFn|null
     */
    private ?\Closure $tagsExtractor;

    /**
     * @var TtlExtractorFn|null
     */
    private ?\Closure $ttlExtractor;

    /**
     * @var ExecutionResultInterface|null
     *
     * @internal
     */
    private ?ExecutionResultInterface $result;

    /**
     * Create new cache identity.
     *
     * Tags extractor and TTL extractor are optional.
     *
     * If tags extractor is provided, it will be invoked within CacheIdentity::getTags() method
     * and provided tags will be merged with tags provided in constructor.
     *
     * If TTL extractor is provided, it will be invoked within CacheIdentity::getTtl() method
     * and provided TTL will be ignored.
     *
     * Neither function will be invoked unless execution result is provided.
     *
     * Do not use constructor directly, use "create()" static method instead.
     *
     * @param string[]|string               $key           Cache key. If array is provided, it will be imploded with '|' separator.
     * @param string[]|null                 $tags          Cache tags. Optional.
     * @param \DateTimeInterface|int|null   $ttl           Cache time-to-live. If NULL, cache item will not expire. Optional, default NULL.
     * @param TagsExtractorFn|null          $tagsExtractor Function to extract cache tags from execution result. Optional.
     * @param TtlExtractorFn|null           $ttlExtractor  Function to extract cache time-to-live from execution result. Optional, default NULL.
     * @param ExecutionResultInterface|null $result        Execution result. Default NULL, will be provided in runtime after execution.
     *
     * @psalm-suppress RiskyTruthyFalsyComparison
     *
     * @internal
     */
    public function __construct(
        array|string                $key,
        ?array                      $tags = null,
        \DateTimeInterface|int|null $ttl = null,
        ?callable                   $tagsExtractor = null,
        ?callable                   $ttlExtractor = null,
        ?ExecutionResultInterface   $result = null,
    ) {
        $this->key           = self::sanitize(\is_array($key) ? \implode('|', $key) : $key);
        $this->tags          = empty($tags) ? [] : \array_unique(\array_map([self::class, 'sanitize'], $tags));
        $this->ttl           = $ttl instanceof \DateTimeInterface ? $ttl->getTimestamp() - \time() : $ttl;
        $this->tagsExtractor = null !== $tagsExtractor ? $tagsExtractor(...) : null;
        $this->ttlExtractor  = null !== $ttlExtractor ? $ttlExtractor(...) : null;
        $this->result        = $result;
    }

    /**
     * Create new instance.
     *
     * @param string[]|string             $key           Cache key. If array is provided, it will be imploded with '|' separator.
     * @param string[]|null               $tags          Cache tags. Optional.
     * @param \DateTimeInterface|int|null $ttl           Cache time-to-live. If NULL, cache item will not expire. Optional, default NULL.
     * @param TagsExtractorFn|null        $tagsExtractor Function to extract cache tags from execution result. Optional.
     * @param TtlExtractorFn|null         $ttlExtractor  Function to extract cache time-to-live from execution result. Optional, default NULL.
     */
    public static function create(
        array|string                $key,
        ?array                      $tags = null,
        \DateTimeInterface|int|null $ttl = null,
        ?callable                   $tagsExtractor = null,
        ?callable                   $ttlExtractor = null,
    ): self {
        return new self($key, $tags, $ttl, $tagsExtractor, $ttlExtractor);
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
        if (null !== $this->tagsExtractor && null !== $this->result) {
            /**
             * @psalm-suppress RedundantConditionGivenDocblockType, DocblockTypeContradiction
             * @phpstan-ignore-next-line
             */
            $extracted = ($this->tagsExtractor)($this->result) ?? [];

            return \array_unique(
                \array_merge(
                    $this->tags,
                    \array_map([self::class, 'sanitize'], $extracted),
                )
            );
        }

        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function getTtl(): ?int
    {
        if (null !== $this->ttlExtractor && null !== $this->result) {
            return ($this->ttlExtractor)($this->result);
        }

        return $this->ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function tag(string ...$tags): self
    {
        return new self(
            $this->key,
            \array_merge($this->tags, $tags),
            $this->ttl,
            $this->tagsExtractor,
            $this->ttlExtractor,
            $this->result,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function withKey(array|string $key): self
    {
        return new self(
            $key,
            $this->tags,
            $this->ttl,
            $this->tagsExtractor,
            $this->ttlExtractor,
            $this->result,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function withTags(?array $tags = null): self
    {
        return new self(
            $this->key,
            $tags,
            $this->ttl,
            $this->tagsExtractor,
            $this->ttlExtractor,
            $this->result,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function withTtl(\DateTimeInterface|int|null $ttl = null): self
    {
        return new self(
            $this->key,
            $this->tags,
            $ttl,
            $this->tagsExtractor,
            $this->ttlExtractor,
            $this->result,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function withExecutionResult(ExecutionResultInterface $result): self
    {
        return new self(
            $this->key,
            $this->tags,
            $this->ttl,
            $this->tagsExtractor,
            $this->ttlExtractor,
            $result,
        );
    }

    /**
     * Sanitize cache key/cache tag value.
     */
    public static function sanitize(string $value): string
    {
        return \str_replace(\str_split(CacheItem::RESERVED_CHARACTERS), '_', $value);
    }
}
