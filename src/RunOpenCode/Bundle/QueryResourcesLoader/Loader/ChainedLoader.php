<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Loader;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\LoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SourceNotFoundException;

final class ChainedLoader implements LoaderInterface
{
    /**
     * @var iterable<LoaderInterface>
     */
    private iterable $loaders;

    /**
     * Simple runtime cache for found loaders.
     *
     * @var array<string, LoaderInterface|null>
     */
    private array $found = [];

    /**
     * @param iterable<LoaderInterface> $loaders
     */
    public function __construct(iterable $loaders)
    {
        $this->loaders = $loaders;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        if (!\array_key_exists($name, $this->found)) {
            $this->locate($name);
        }

        return null !== $this->found[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name, array $args = []): string
    {
        if ($this->has($name)) {
            /** @var LoaderInterface $loader */
            $loader = $this->found[$name];

            return $loader->get($name, $args);
        }

        throw new SourceNotFoundException(\sprintf(
            'Could not find query source "%s" in any of chained loaders.',
            $name
        ));
    }

    private function locate(string $name): void
    {
        foreach ($this->loaders as $loader) {
            if ($loader->has($name)) {
                $this->found[$name] = $loader;
                return;
            }
        }

        $this->found[$name] = null;
    }

}
