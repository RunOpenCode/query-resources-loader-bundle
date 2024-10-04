<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

/**
 * Represents a class which has its own cache identity.
 *
 * This interface is useful for classes that are some kind of
 * value objects or data transfer objects (DTOs) used as input
 * of repository methods or query handlers.
 *
 * If those value objects/DTOs should yield same query results
 * for same input, they could be used for constructing a cache
 * identity.
 *
 * Therefore, this interface is useful for caching middleware
 * to identify cache item for execution result. You may implement
 * this interface, or you can just use trait
 * \RunOpenCode\Bundle\QueryResourcesLoader\Cache\HasCacheIdentity
 * to speed up your development.
 *
 * @see \RunOpenCode\Bundle\QueryResourcesLoader\Cache\HasCacheIdentity
 */
interface CacheIdentifiableInterface
{
    /**
     * Get cache identity.
     *
     * Get cache identity for execution result which can be identified
     * by values contained by this object.
     */
    public function getCacheIdentity(): CacheIdentityInterface;
}
