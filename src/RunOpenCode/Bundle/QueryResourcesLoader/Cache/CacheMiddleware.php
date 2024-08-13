<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Cache;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\MiddlewareInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Cache middleware.
 *
 * Cache middleware is responsible for caching of the query results.
 */
final readonly class CacheMiddleware implements MiddlewareInterface
{
    public function __construct(
        private CacheInterface $cache = new TagAwareAdapter(new ArrayAdapter()),
        private ?int           $defaultTtl = null,
    ) {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(string $query, Parameters $parameters, Options $options, callable $next): ExecutionResultInterface
    {
        if (null === $options->cache) {
            return $next($query, $parameters, $options);
        }

        $identity = $options->cache;
        $identity = $identity->withTtl($options->cache->ttl ?? $this->defaultTtl);

        return $this->cache->get($identity->key, function(ItemInterface $item, bool &$save) use ($query, $parameters, $identity, $options, $next): ExecutionResultInterface {
            $result = $next($query, $parameters, $options);
            $save   = true;

            $item->set($result);
            $item->expiresAfter($identity->ttl);

            if (!empty($identity->tags)) {
                $item->tag($identity->tags);
            }

            return $result;
        });
    }
}
