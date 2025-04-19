<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Loader;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\LoaderInterface;

/**
 * Raw loader assumes that provided string is SQL query and does not
 * require any processing.
 *
 * This loader should be last in the chain of loaders.
 */
final readonly class RawLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name, array $args = []): string
    {
        return $name;
    }
}
