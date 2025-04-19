<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Types\Types;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;

/**
 * Query parameters for Dbal executor.
 *
 * Extension of Parameters for Dbal executor with additional utility
 * function when registering parameters with types. All known types
 * of Doctrine Dbal for parameters are available.
 *
 * For additional, custom registered types, extend this class and add
 * additional methods for registering parameters with custom types.
 *
 * ```php
 * DbalParameters::create()
 *  ->string('name', 'John Doe')
 *  ->integer('age', 30)
 * ```
 *
 * @method array<string, string> getTypes()
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class DbalParameters extends Parameters
{
    final public static function from(Parameters $parameters): self
    {
        if ($parameters instanceof DbalParameters) {
            return $parameters;
        }

        return self::create($parameters->getParameters(), $parameters->getTypes());
    }

    final public function asciiString(string $name, \Stringable|string|null $value): self
    {
        $value = $value instanceof \Stringable ? (string)$value : $value;

        return $this->set($name, $value, Types::ASCII_STRING);
    }

    final public function bigint(string $name, ?int $value): self
    {
        return $this->set($name, $value, Types::BIGINT);
    }

    final public function binary(string $name, mixed $value): self
    {
        return $this->set($name, $value, Types::BINARY);
    }

    final public function blob(string $name, mixed $value): self
    {
        return $this->set($name, $value, Types::BLOB);
    }

    final public function boolean(string $name, ?bool $value): self
    {
        return $this->set($name, $value, Types::BOOLEAN);
    }

    final public function date(string $name, ?\DateTimeInterface $value): self
    {
        return $this->set($name, $value, Types::DATE_MUTABLE);
    }

    final public function dateImmutable(string $name, ?\DateTimeInterface $value): self
    {
        $value = null !== $value && !$value instanceof \DateTimeImmutable ? \DateTimeImmutable::createFromInterface($value) : $value;

        return $this->set($name, $value, Types::DATE_IMMUTABLE);
    }

    final public function dateInterval(string $name, ?\DateInterval $value): self
    {
        return $this->set($name, $value, Types::DATEINTERVAL);
    }

    final public function dateTime(string $name, ?\DateTimeInterface $value): self
    {
        return $this->set($name, $value, Types::DATETIME_MUTABLE);
    }

    final public function dateTimeImmutable(string $name, ?\DateTimeInterface $value): self
    {
        $value = null !== $value && !$value instanceof \DateTimeImmutable ? \DateTimeImmutable::createFromInterface($value) : $value;

        return $this->set($name, $value, Types::DATETIME_IMMUTABLE);
    }

    final public function dateTimeTz(string $name, ?\DateTimeInterface $value): self
    {
        return $this->set($name, $value, Types::DATETIMETZ_MUTABLE);
    }

    final public function dateTimeTzImmutable(string $name, ?\DateTimeInterface $value): self
    {
        $value = null !== $value && !$value instanceof \DateTimeImmutable ? \DateTimeImmutable::createFromInterface($value) : $value;

        return $this->set($name, $value, Types::DATETIMETZ_IMMUTABLE);
    }

    final public function decimal(string $name, ?float $value): self
    {
        return $this->set($name, $value, Types::DECIMAL);
    }

    final public function float(string $name, ?float $value): self
    {
        return $this->set($name, $value, Types::FLOAT);
    }

    final public function guid(string $name, \Stringable|string|null $value): self
    {
        return $this->set($name, $value, Types::GUID);
    }

    final public function integer(string $name, ?int $value): self
    {
        return $this->set($name, $value, Types::INTEGER);
    }

    final public function json(string $name, mixed $value): self
    {
        return $this->set($name, $value, Types::JSON);
    }

    /**
     * @param string       $name  Parameter name.
     * @param mixed[]|null $value Parameter value.
     *
     * @return self
     */
    final public function simpleArray(string $name, ?array $value): self
    {
        return $this->set($name, $value, Types::SIMPLE_ARRAY);
    }

    final public function smallInt(string $name, ?int $value): self
    {
        return $this->set($name, $value, Types::SMALLINT);
    }

    final public function string(string $name, \Stringable|string|null $value): self
    {
        $value = $value instanceof \Stringable ? (string)$value : $value;

        return $this->set($name, $value, Types::STRING);
    }

    final public function text(string $name, \Stringable|string|null $value): self
    {
        $value = $value instanceof \Stringable ? (string)$value : $value;

        return $this->set($name, $value, Types::TEXT);
    }

    final public function time(string $name, ?\DateTimeInterface $value): self
    {
        return $this->set($name, $value, Types::TIME_MUTABLE);
    }

    final public function timeImmutable(string $name, ?\DateTimeInterface $value): self
    {
        $value = null !== $value && !$value instanceof \DateTimeImmutable ? \DateTimeImmutable::createFromInterface($value) : $value;

        return $this->set($name, $value, Types::TIME_IMMUTABLE);
    }

    /**
     * Set parameter value as enum.
     *
     * For backed enums, the value will be converted to the backing type, which means
     * that either string or integer parameter will be set.
     *
     * For non-backed enums, the value will be extracted from the enum name, which means
     * that the parameter will be set as string.
     *
     * In general, this is shorthand for:
     *
     * ```php
     * // Backed enums
     * $parameters->set($name, $value->value, Types::STRING);
     * $parameters->set($name, $value->value, Types::INTEGER);
     * // Non-backed enums
     * $parameters->set($name, $value->name, Types::STRING);
     * ```
     *
     * @param string    $name  Parameter name.
     * @param \UnitEnum $value Parameter value.
     *
     * @return self Fluent return.
     */
    final public function enum(string $name, \UnitEnum $value): self
    {
        $reflection = new \ReflectionEnum($value::class);

        if (!$reflection->isBacked()) {
            return $this->string($name, $value->name);
        }

        /** @psalm-suppress PossiblyNullReference, UndefinedMethod */
        if ('string' === $reflection->getBackingType()->getName()) {
            // @phpstan-ignore-next-line
            return $this->string($name, $value->value);
        }

        // @phpstan-ignore-next-line
        return $this->integer($name, $value->value);
    }

    /**
     * Set parameter value as array of integers.
     *
     * If you provide null value, or empty iterable, the parameter will be set to null.
     *
     * @param string         $name  Parameter name.
     * @param ?iterable<int> $value Parameter value.
     *
     * @return self Fluent return.
     */
    final public function integerArray(string $name, ?iterable $value): self
    {
        if (null === $value) {
            return $this->set($name, null, ArrayParameterType::INTEGER);
        }

        $value = \is_array($value) ? $value : \iterator_to_array($value);

        if (0 === \count($value)) {
            return $this->set($name, null, ArrayParameterType::INTEGER);
        }

        return $this->set($name, $value, ArrayParameterType::INTEGER);
    }

    /**
     * Set parameter value as array of strings.
     *
     * If you provide null value, or empty iterable, the parameter will be set to null.
     *
     * @param string                        $name  Parameter name.
     * @param ?iterable<\Stringable|string> $value Parameter value.
     *
     * @return self Fluent return.
     */
    final public function stringArray(string $name, ?iterable $value): self
    {
        if (null === $value) {
            return $this->set($name, null, ArrayParameterType::INTEGER);
        }

        $value = \is_array($value) ? $value : \iterator_to_array($value);

        if (0 === \count($value)) {
            return $this->set($name, null, ArrayParameterType::STRING);
        }

        return $this->set(
            $name,
            \array_map(
                static fn(\Stringable|string $current): string => (string)$current,
                $value
            ),
            ArrayParameterType::STRING
        );
    }

    /**
     * Set parameter value as array of ascii strings.
     *
     * If you provide null value, or empty iterable, the parameter will be set to null.
     *
     * @param string                        $name  Parameter name.
     * @param ?iterable<\Stringable|string> $value Parameter value.
     *
     * @return self Fluent return.
     */
    final public function asciiArray(string $name, ?iterable $value): self
    {
        if (null === $value) {
            return $this->set($name, null, ArrayParameterType::ASCII);
        }

        $value = \is_array($value) ? $value : \iterator_to_array($value);

        if (0 === \count($value)) {
            return $this->set($name, null, ArrayParameterType::ASCII);
        }

        return $this->set(
            $name,
            \array_map(
                static fn(\Stringable|string $current): string => (string)$current,
                $value
            ),
            ArrayParameterType::ASCII
        );
    }

    /**
     * Set parameter value as array of binary values.
     *
     * If you provide null value, or empty iterable, the parameter will be set to null.
     *
     * @param string           $name  Parameter name.
     * @param ?iterable<mixed> $value Parameter value.
     *
     * @return self Fluent return.
     */
    final public function binaryArray(string $name, ?iterable $value): self
    {
        if (null === $value) {
            return $this->set($name, null, ArrayParameterType::BINARY);
        }

        $value = \is_array($value) ? $value : \iterator_to_array($value);

        if (0 === \count($value)) {
            return $this->set($name, null, ArrayParameterType::BINARY);
        }

        return $this->set($name, $value, ArrayParameterType::BINARY);
    }

    /**
     * Set parameter value as array of enum values.
     *
     * If you provide null value, or empty iterable, the parameter will be set to null.
     *
     * You may used mixed types of enums in the array, but it is not recommended. If only
     * integer backed enums are used, the parameter will be set as integer array. Otherwise,
     * the parameter will be set as string array.
     *
     * @param string               $name  Parameter name.
     * @param ?iterable<\UnitEnum> $value Parameter value.
     *
     * @return self
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    final public function enumArray(string $name, ?iterable $value): self
    {
        if (null === $value) {
            return $this->set($name, null, ArrayParameterType::STRING);
        }

        $value = \is_array($value) ? $value : \iterator_to_array($value);

        if (0 === \count($value)) {
            return $this->set($name, null, ArrayParameterType::BINARY);
        }

        $hasString = false;
        $values    = [];

        foreach ($value as $item) {
            $reflection = new \ReflectionEnum($item);

            if (!$reflection->isBacked()) {
                $values[]  = $item->name;
                $hasString = true;
                continue;
            }

            // @phpstan-ignore-next-line
            $values[] = $item->value;

            /** @psalm-suppress PossiblyNullReference, UndefinedMethod */
            if ('string' === $reflection->getBackingType()->getName()) {
                $hasString = true;
            }
        }

        if ($hasString) {
            return $this->set($name, $values, ArrayParameterType::STRING);
        }

        return $this->set($name, $values, ArrayParameterType::INTEGER);
    }
}
