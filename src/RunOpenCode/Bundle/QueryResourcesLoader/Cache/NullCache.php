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

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheInterface;

/**
 * Class NullCache
 *
 * NullCache is null cache implementation suitable for development environment.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\Cache
 */
class NullCache implements CacheInterface
{
    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getStats()
    {
        return null;
    }
}
