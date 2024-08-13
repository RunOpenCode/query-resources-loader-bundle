<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SourceNotFoundException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SyntaxException;

/**
 * Loader loads and parses query.
 */
interface LoaderInterface
{
    /**
     * Check if manager have the query source code by its given name.
     *
     * @param string $name The name of the query source to check if can be loaded.
     *
     * @return bool TRUE If the query source code is handled by this manager or not.
     */
    public function has(string $name): bool;

    /**
     * Get Query source by its name.
     *
     * @param string               $name Name of query source code.
     * @param array<string, mixed> $args Arguments for modification/compilation of query source code.
     *
     * @return string SQL statement.
     *
     * @throws SourceNotFoundException If query source code is not found.
     * @throws SyntaxException If query source code contains syntax error.
     * @throws RuntimeException If any other error occurred.
     */
    public function get(string $name, array $args = []): string;
}
