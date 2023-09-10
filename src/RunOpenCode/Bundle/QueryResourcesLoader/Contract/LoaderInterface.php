<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

/**
 * Loader loads and parses query.
 */
interface LoaderInterface
{
    /**
     * Check if manager have the Query source code by its given name.
     *
     * @param string $name The name of the Query source to check if can be loaded.
     *
     * @return bool TRUE If the Query source code is handled by this manager or not.
     */
    public function has(string $name): bool;

    /**
     * Get Query source by its name.
     *
     * @param string               $name Name of Query source code.
     * @param array<string, mixed> $args Arguments for modification/compilation of Query source code.
     *
     * @return string SQL statement.
     */
    public function get(string $name, array $args = []): string;
}
