<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Model;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentifiableInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentityInterface;

/**
 * Execution options.
 *
 * This class is used to store options for executor.
 *
 * Extend this class to add more options related to
 * executor low level implementation.
 *
 * @implements \IteratorAggregate<string, mixed>
 * @implements \ArrayAccess<string, mixed>
 *
 * @property string|null $executor
 * @property CacheIdentityInterface|CacheIdentifiableInterface|null $cache
 * @property string|null $loader
 *
 * @method string|null getExecutor()
 * @method CacheIdentityInterface|CacheIdentifiableInterface|null getCache()
 * @method string|null getLoader()
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
readonly class Options implements \IteratorAggregate, \ArrayAccess
{
    /**
     * @param array<string, mixed> $options
     */
    final private function __construct(
        private array $options
    ) {
        // noop
    }

    /**
     * Create new instance of options.
     *
     * @param array<string, mixed> $options Options.
     *
     * @return static New options instance.
     */
    public static function create(array $options = []): static
    {
        return new static($options);
    }

    /**
     * Convert options to instance of this class.
     *
     * @param Options|array<string, mixed> ...$options
     *
     * @psalm-suppress NamedArgumentNotAllowed
     */
    public static function from(Options|array ...$options): static
    {
        return static::create(\array_merge(...\array_map(
            static fn(Options|array $option): array => $option instanceof Options ? $option->toArray() : $option,
            $options
        )));
    }

    /**
     * Create new instance of options with executor.
     */
    public static function executor(string $executor): static
    {
        return static::create([
            'executor' => $executor,
        ]);
    }

    /**
     * Create new instance of options with cache and default executor.
     */
    public static function cached(CacheIdentityInterface|CacheIdentifiableInterface $cache): static
    {
        return static::create([
            'cache' => $cache,
        ]);
    }

    /**
     * Create new instance of options with custom loader and default executor.
     */
    public static function loader(string $loader): static
    {
        return static::create([
            'loader' => $loader,
        ]);
    }

    /**
     * @return array<string, mixed> Options.
     */
    public function toArray(): array
    {
        return $this->options;
    }

    public function __get(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return \array_key_exists($name, $this->options);
    }

    /**
     * @param mixed[] $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (\str_starts_with($name, 'with') && 1 === \count($arguments)) {
            $name = \lcfirst(\substr($name, 4));
            return self::from($this, [$name => $arguments[0]]);
        }

        if (\str_starts_with($name, 'get') && 0 === \count($arguments)) {
            $name = \lcfirst(\substr($name, 3));
            return $this->options[$name] ?? null;
        }

        throw new \BadMethodCallException(\sprintf(
            'Undefined method "%s" called.',
            $name
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->options[$offset] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \BadMethodCallException('Options are immutable.');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new \BadMethodCallException('Options are immutable.');
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->options);
    }

    /**
     * Set executor.
     */
    public function withExecutor(?string $executor): static
    {
        return static::create(\array_merge(
            $this->options,
            ['executor' => $executor]
        ));
    }

    /**
     * Set cache.
     */
    public function withCache(CacheIdentityInterface|CacheIdentifiableInterface|null $cache): static
    {
        return static::create(\array_merge(
            $this->options,
            ['cache' => $cache]
        ));
    }

    /**
     * Set loader.
     */
    public function withLoader(?string $loader): static
    {
        return static::create(\array_merge(
            $this->options,
            ['loader' => $loader]
        ));
    }

    /**
     * Set arbitrary option.
     */
    public function withOption(string $name, mixed $value): static
    {
        return static::create(\array_merge(
            $this->options,
            [$name => $value]
        ));
    }
}
