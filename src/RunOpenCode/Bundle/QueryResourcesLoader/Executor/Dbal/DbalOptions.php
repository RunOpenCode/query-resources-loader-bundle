<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal;

use Doctrine\DBAL\TransactionIsolationLevel;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;

/**
 * Options for Dbal executor.
 *
 * @property TransactionIsolationLevel|int|null $isolation
 *
 * @method TransactionIsolationLevel|int|null getIsolation()
 *
 * @psalm-suppress MoreSpecificReturnType, LessSpecificReturnStatement
 */
readonly class DbalOptions extends Options
{
    /**
     * @param Options|array<string, mixed> $options
     *
     * @return static
     */
    public static function readUncommitted(Options|array $options = []): static
    {
        return static::from($options, [
            'isolation' => TransactionIsolationLevel::READ_UNCOMMITTED,
        ]);
    }

    /**
     * @param Options|array<string, mixed> $options
     *
     * @return static
     */
    public static function readCommitted(Options|array $options = []): static
    {
        return static::from($options, [
            'isolation' => TransactionIsolationLevel::READ_COMMITTED,
        ]);
    }

    /**
     * @param Options|array<string, mixed> $options
     *
     * @return static
     */
    public static function repeatableRead(Options|array $options = []): static
    {
        return static::from($options, [
            'isolation' => TransactionIsolationLevel::REPEATABLE_READ,
        ]);
    }

    /**
     * @param Options|array<string, mixed> $options
     *
     * @return static
     */
    public static function serializable(Options|array $options = []): static
    {
        return static::from($options, [
            'isolation' => TransactionIsolationLevel::SERIALIZABLE,
        ]);
    }

    public function withIsolation(TransactionIsolationLevel|int $level): static
    {
        return static::from($this, [
            'isolation' => $level,
        ]);
    }

    public function withReadUncommitted(): static
    {
        return $this->withIsolation(TransactionIsolationLevel::READ_UNCOMMITTED);
    }

    public function withReadCommitted(): static
    {
        return $this->withIsolation(TransactionIsolationLevel::READ_COMMITTED);
    }

    public function withRepeatableRead(): static
    {
        return $this->withIsolation(TransactionIsolationLevel::REPEATABLE_READ);
    }

    public function withSerializable(): static
    {
        return $this->withIsolation(TransactionIsolationLevel::SERIALIZABLE);
    }
}
