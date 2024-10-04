<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Cache;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentifiableInterface;
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

        /** @var CacheIdentity $identity */
        $identity = $options->cache instanceof CacheIdentifiableInterface ?
            $options->cache->getCacheIdentity()
            : $options->cache;


        // ensure TTL is set
        $identity = $identity->withTtl($identity->getTtl() ?? $this->defaultTtl);

        return $this->cache->get($identity->getKey(), function(ItemInterface $item, bool &$save) use ($query, $parameters, $identity, $options, $next): ExecutionResultInterface {
            $result = $next($query, $parameters, $options);
            $save   = true;

            $item->set($result);
            $item->expiresAfter($identity->getTtl());

            if (!empty($identity->getTags())) {
                $item->tag($identity->getTags());
            }

            return $result;
        });
    }
}
