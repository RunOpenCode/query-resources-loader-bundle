<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Model;

use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheIdentity;

/**
 * Execution options.
 *
 * This class is used to store options for executor.
 *
 * Extend this class to add more options related to
 * executor low level implementation.
 */
readonly class Options
{
    /**
     * Executor name (NULL for default one).
     */
    public ?string $executor;

    /**
     * Cache identity.
     */
    public ?CacheIdentity $cache;

    protected function __construct(
        ?string         $executor = null,
        ?CacheIdentity $cache = null,
    ) {
        $this->executor = $executor;
        $this->cache    = $cache;
    }

    public static function executor(string $executor, ?CacheIdentity $cache = null): static
    {
        return static::create([
            'executor' => $executor,
            'cache'    => $cache,
        ]);
    }

    /**
     * Create new instance of options.
     *
     * @param array{
     *     executor?: string|null,
     *     cache?: CacheIdentity|null
     * } $options Options to create new instance of options.
     *
     * @return static New options instance.
     */
    public static function create(array $options = []): static
    {
        $options['executor'] = $options['executor'] ?? null;
        $options['cache']    = $options['cache'] ?? null;

        if (static::class === self::class) {
            /** @psalm-suppress LessSpecificReturnStatement */
            return new self($options['executor'], $options['cache']); // @phpstan-ignore-line
        }

        $reflection  = new \ReflectionClass(static::class);
        $constructor = null;

        while (null === $constructor) {
            $constructor = $reflection->getConstructor();
            $reflection  = $reflection->getParentClass();

            \assert($reflection instanceof \ReflectionClass);
        }

        $parameters = $constructor->getParameters();
        $arguments  = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            if (\array_key_exists($name, $options)) {
                $arguments[] = $options[$name];
                continue;
            }

            $arguments[] = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
        }

        /**
         * @psalm-suppress PossiblyInvalidCast, UnsafeInstantiation, PossiblyNullArgument, PossiblyInvalidArgument
         * @phpstan-ignore-next-line
         */
        return new static(...$arguments);
    }

    public function withExecutor(?string $executor): static
    {
        /**
         * @psalm-suppress InvalidArgument
         * @phpstan-ignore-next-line
         */
        return static::create(\array_merge(
            \get_object_vars($this),
            ['executor' => $executor]
        ));
    }

    public function withCache(CacheIdentity $cache): static
    {
        /**
         * @psalm-suppress InvalidArgument
         * @phpstan-ignore-next-line
         */
        return static::create(\array_merge(
            \get_object_vars($this),
            ['cache' => $cache]
        ));
    }
}
