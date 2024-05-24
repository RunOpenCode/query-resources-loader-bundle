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

    /**
     * @var iterable<string>
     */
    private iterable $iterator;

    /**
     * @param iterable<string> $iterator
     */
    public function __construct(Environment $twig, iterable $iterator)
    {
        $this->twig     = $twig;
        $this->iterator = $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        foreach ($this->iterator as $template) {
            try {
                $this->twig->load($template);
            } catch (Error $error) {
                // noop
            }
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional(): bool
    {
        return true;
    }
}
