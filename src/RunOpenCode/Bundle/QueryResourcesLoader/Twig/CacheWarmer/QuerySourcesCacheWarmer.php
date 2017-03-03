<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Twig\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Class QuerySourcesCacheWarmer
 *
 * Warms up queries cache.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\Twig\CacheWarmer
 */
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
                $this->twig->load($template);
            } catch (\Twig_Error $e) {
                // noop
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
