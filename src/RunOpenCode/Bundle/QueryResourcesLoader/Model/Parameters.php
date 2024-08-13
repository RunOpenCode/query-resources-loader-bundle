<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Model;

use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalParameters;

/**
 * Query parameters.
 *
 * This class is used to store query parameters and their types. Parameters
 * will be used for compiling query source as well  as for executing query.
 *
 * Extend this class to suit needs of your executor.
 *
 * ```php
 * Parameters::create()
 *  ->set('name', 'John Doe', 'string')
 *  ->set('age', 30, 'integer')
 * ```
 */
class Parameters
{
    /**
     * @var array<string, mixed>
     */
    private array $parameters = [];

    /**
     * @var array<string, \UnitEnum|string|int|null>
     */
    private array $types = [];

    /**
     * @param array<string, mixed>                     $parameters
     * @param array<string, \UnitEnum|string|int|null> $types
     */
    final private function __construct(array $parameters = [], array $types = [])
    {
        foreach ($parameters as $name => $value) {
            $this->set($name, $value, $types[$name] ?? null);
        }
    }

    /**
     * Create new instance of parameters.
     *
     * @param array<string, mixed>                     $parameters Initial parameters to set.
     * @param array<string, \UnitEnum|string|int|null> $types      Initial types to set for given parameters. Extra will be ignored.
     *
     * @return static Fluent return.
     */
    final public static function create(array $parameters = [], array $types = []): static
    {
        return new static($parameters, $types);
    }

    /**
     * Get all parameters.
     *
     * @return array<string, mixed> Array of parameters.
     */
    final public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get all types.
     *
     * @return array<string, \UnitEnum|string|int|null> Array of types.
     */
    final public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Check if parameter exists.
     *
     * @param string $name Parameter name.
     */
    final public function has(string $name): bool
    {
        return \array_key_exists($name, $this->parameters);
    }

    /**
     * Get parameter value and type.
     *
     * @param string $name Parameter name.
     *
     * @return array{mixed, \UnitEnum|string|int|null} Tuple containing parameter value and type.
     *
     * @example
     *
     * [$value, $type] = $parameters->get('name');
     */
    final public function get(string $name): array
    {
        return [
            $this->parameters[$name],
            $this->types[$name] ?? null,
        ];
    }

    /**
     * Set parameter value.
     *
     * @param string                    $name  Parameter name.
     * @param mixed                     $value Parameter value.
     * @param \UnitEnum|string|int|null $type  Parameter type, if any.
     *
     * @return static Fluent return.
     */
    final public function set(string $name, mixed $value, \UnitEnum|string|int|null $type = null): static
    {
        $this->parameters[$name] = $value;

        if (null !== $type) {
            $this->types[$name] = $type;
        }

        return $this;
    }

    /**
     * Remove parameter.
     *
     * @param string $name Parameter name to remove.
     *
     * @return static Fluent return.
     */
    final public function remove(string $name): static
    {
        unset(
            $this->parameters[$name],
            $this->types[$name],
        );

        return $this;
    }
}
