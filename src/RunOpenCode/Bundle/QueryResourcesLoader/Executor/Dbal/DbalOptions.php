<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal;

use Doctrine\DBAL\TransactionIsolationLevel;
use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheIdentity;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;

/**
 * Options for Dbal executor.
 */
final readonly class DbalOptions extends Options
{
    public TransactionIsolationLevel|int|null $isolation;

    public function __construct(
        ?string                            $executor = null,
        ?CacheIdentity                     $cache = null,
        TransactionIsolationLevel|int|null $isolation = null,
    ) {
        parent::__construct($executor, $cache);
        $this->isolation = $isolation;
    }

    public static function from(Options $options): DbalOptions
    {
        if ($options instanceof DbalOptions) {
            return $options;
        }

        return new DbalOptions(
            $options->executor,
            $options->cache,
        );
    }

    /**
     * Create new instance of options.
     *
     * @param array{
     *     executor?: string|null,
     *     cache?: CacheIdentity|null,
     *     isolation?: TransactionIsolationLevel|int|null,
     * } $options Options to create new instance of options.
     *
     * @return static New options instance.
     */
    public static function create(array $options = []): static
    {
        return new self(
            $options['executor'] ?? null,
            $options['cache'] ?? null,
            $options['isolation'] ?? null,
        );
    }

    public static function readUncommitted(
        ?string        $executor = null,
        ?CacheIdentity $cache = null,
    ): self {
        return new self($executor, $cache, TransactionIsolationLevel::READ_UNCOMMITTED);
    }

    public static function readCommitted(
        ?string        $executor = null,
        ?CacheIdentity $cache = null,
    ): self {
        return new self($executor, $cache, TransactionIsolationLevel::READ_COMMITTED);
    }

    public static function repeatableRead(
        ?string        $executor = null,
        ?CacheIdentity $cache = null,
    ): self {
        return new self($executor, $cache, TransactionIsolationLevel::REPEATABLE_READ);
    }

    public static function serializable(
        ?string        $executor = null,
        ?CacheIdentity $cache = null,
    ): self {
        return new self($executor, $cache, TransactionIsolationLevel::SERIALIZABLE);
    }

    public function withIsolationLevel(TransactionIsolationLevel|int $level): self
    {
        return new self($this->executor, $this->cache, $level);
    }
}
