<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Cache;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentifiableInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentityInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultAwareInterface;
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
    public const TAG = 'runopencode_query_resources_loader_cache';

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

        /**
         * @psalm-suppress UnnecessaryVarAnnotation
         * @var CacheIdentityInterface $identity
         */
        $identity = $options->cache instanceof CacheIdentifiableInterface ?
            $options->cache->getCacheIdentity()
            : $options->cache;


        // ensure TTL is set
        $identity = $identity->withTtl($identity->getTtl() ?? $this->defaultTtl);

        return $this->cache->get($identity->getKey(), function(ItemInterface $item, bool &$save) use ($query, $parameters, $identity, $options, $next): ExecutionResultInterface {
            $result = $next($query, $parameters, $options);
            $save   = true;

            $item->set($result);

            // If cache identity is aware of execution result, we can pass it
            // to it in order to mutate tags and/or TTL.
            if ($identity instanceof ExecutionResultAwareInterface) {
                $identity = $identity->withExecutionResult($result);
            }

            /**
             * @psalm-suppress UnnecessaryVarAnnotation
             * @var CacheIdentityInterface $identity
             */
            $item->expiresAfter($identity->getTtl());
            
            if (method_exists($item, 'tag')) {
                $item->tag(\array_merge(
                    $identity->getTags(),
                    [self::TAG]
                ));
            }

            return $result;
        });
    }
}
