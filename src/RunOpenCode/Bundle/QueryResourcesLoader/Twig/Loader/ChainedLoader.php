<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Twig\Loader;

use Twig\Loader\ChainLoader as TwigChainLoader;
use Twig\Loader\LoaderInterface;
use Twig\Source;

/**
 * Replace this class with decorated when the following issue is resolved: https://github.com/twigphp/Twig/issues/4200
 *
 * @see https://github.com/twigphp/Twig/issues/4200
 */
final class ChainedLoader implements LoaderInterface
{
    private TwigChainLoader $loader;

    /**
     * @param iterable<LoaderInterface> $loaders
     */
    public function __construct(iterable $loaders = [])
    {
        $this->loader = new TwigChainLoader(
            \is_array($loaders) ? $loaders : \iterator_to_array($loaders, false)
        );
    }

    public function getSourceContext(string $name): Source
    {
        return $this->loader->{__FUNCTION__}(...\func_get_args());
    }

    public function getCacheKey(string $name): string
    {
        return $this->loader->{__FUNCTION__}(...\func_get_args());
    }

    public function isFresh(string $name, int $time): bool
    {
        return $this->loader->{__FUNCTION__}(...\func_get_args());
    }

    public function exists(string $name)
    {
        return $this->loader->{__FUNCTION__}(...\func_get_args());
    }

    /**
     * @param mixed[] $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->loader->{$name}(...$arguments);
    }

    public function __get(string $name): mixed
    {
        return $this->loader->{$name};
    }

    public function __set(string $name, mixed $value): void
    {
        $this->loader->{$name} = $value;
    }
}
