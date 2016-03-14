<?php
/*
 * This file is part of the QueryResourcesLoader Bundle, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Cache;

use Doctrine\Common\Cache\Cache;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheInterface;

/**
 * Class DoctrineCacheProxy
 *
 * Doctrine cache proxy implementation, if 'doctrine/cache' library ought to be used.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\Cache
 */
class DoctrineCacheProxy implements CacheInterface
{
    private $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        return $this->cache->fetch($id);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id)
    {
        return $this->cache->contains($id);
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return $this->cache->save($id, $data, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->cache->delete($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getStats()
    {
        return $this->cache->getStats();
    }
}
