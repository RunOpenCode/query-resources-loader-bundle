<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

/**
 * Manager service provides Query source code from loaders, modifying it, if needed, as per concrete implementation of
 * relevant manager and supported scripting language. Manager can execute a Query as well.
 */
interface ManagerInterface
{
    /**
     * Get Query source by its name.
     *
     * @param string               $name Name of Query source code.
     * @param array<string, mixed> $args Arguments for modification/compilation of Query source code.
     *
     * @return string SQL statement.
     */
    public function get(string $name, array $args = []): string;

    /**
     * Execute Query source.
     *
     * @param string                    $name     Name of Query source code.
     * @param array<string, mixed>      $args     Arguments for modification/compilation of Query source code, as well as params for query statement.
     * @param array<string, string|int> $types    Types of parameters for prepared statement.
     * @param array<string, mixed>      $options  Any executor specific options (depending on concrete driver).
     * @param null|string               $executor Executor name.
     *
     * @return ExecutionResultInterface Execution results.
     */
    public function execute(string $name, array $args = [], array $types = [], array $options = [], ?string $executor = null): ExecutionResultInterface;

    /**
     * Check if manager have the Query source code by its given name.
     *
     * @param string $name The name of the Query source to check if can be loaded.
     *
     * @return bool TRUE If the Query source code is handled by this manager or not.
     */
    public function has(string $name): bool;
}
