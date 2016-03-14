<?php
/*
 * This file is part of the QueryResourcesLoader Bundle, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Loader;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\LoaderInterface;

/**
 * Class CachedLoader
 *
 * Cached loader wraps concrete loader and keeps once loaded source code into cache.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\Loader
 */
class CachedLoader implements LoaderInterface
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var array
     */
    private $exists = array();

    public function __construct(LoaderInterface $loader, CacheInterface $cache)
    {
        $this->loader = $loader;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function load($name)
    {
        if ($this->cache->contains($name)) {
            return $this->cache->fetch($name);
        }

        $this->cache->save($name, ($source = $this->loader->load($name)));

        return $source;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        if ($this->cache->contains($name)) {
            return true;
        }

        if (!isset($this->exists[$name])) {
            $this->exists[$name] = $this->loader->exists($name);
        }

        return $this->exists[$name];
    }
}
