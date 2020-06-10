<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Twig\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Twig\Environment;
use Twig\Error\Error;

/**
 * Warms up queries cache.
 */
final class QuerySourcesCacheWarmer implements CacheWarmerInterface
{
    private Environment $twig;

    private iterable $iterator;

    public function __construct(Environment $twig, iterable $iterator)
    {
        $this->twig     = $twig;
        $this->iterator = $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp(string $cacheDir): void
    {
        foreach ($this->iterator as $template) {
            try {
                $this->twig->load($template);
            } catch (Error $error) {
                // noop
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional(): bool
    {
        return true;
    }
}
