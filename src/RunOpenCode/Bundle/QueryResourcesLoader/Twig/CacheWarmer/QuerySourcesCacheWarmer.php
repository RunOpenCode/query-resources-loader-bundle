<?php

namespace RunOpenCode\Bundle\QueryResourcesLoader\Twig\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

class QuerySourcesCacheWarmer implements CacheWarmerInterface
{
    private $twig;
    private $iterator;

    public function __construct(\Twig_Environment $twig, \Traversable $iterator)
    {
        $this->twig = $twig;
        $this->iterator = $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->iterator as $template) {
            try {
                $this->twig->loadTemplate($template);
            } catch (\Twig_Error $e) {
                // problem during compilation, give up
                // might be a syntax error or a non-Twig template
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }
}
