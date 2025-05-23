<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Bundle configuration tree.
 *
 * @psalm-suppress all
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('runopencode_query_resources_loader');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_ttl')
                            ->defaultValue(null)
                            ->info('Default time-to-live for cache items. If not stated, cache items will not expire.')
                        ->end()
                        ->scalarNode('pool')
                            ->defaultValue('cache.app')
                            ->info(<<<EOT
Cache pool to use for caching.

By default, "cache.app" pool will be used. Make sure you provide taggable adapter if you intend to use tags. You may set
this value to "null" if you want to disable caching, which will use only in-memory cache.
EOT)
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('default_executor')
                    ->defaultValue(null)
                    ->info(<<<EOT
Default executor that will be used in "execute" calls.

If not stated, first registered executor will be default one. In order to set default executor, you must provide its name.
For Dbal executors, name of the connection can be used as well.
EOT)
                ->end()
                ->append(new TwigNodeDefinition())
                ->scalarNode('default_loader')
                    ->defaultValue('twig')
                    ->info(<<<EOT
Which loader to use by default, if not specified. By default, "twig" loader will be used.

Library provides "twig" loader which uses Twig as template engine. There is "raw" loader as well, which does not process
provided value, assuming that raw query is provided. There is "chained" loader which can be used to chain multiple loaders
looking for query in each of them. If no loader is found, a "raw" loader will be used.

You may provide your own loader by implementing LoaderInterface and registering it as service. In that case, you may provide
name of the service as loader name.
EOT)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
