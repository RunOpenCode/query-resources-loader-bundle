<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Bundle configuration tree.
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
                ->scalarNode('default_executor')
                    ->defaultValue(null)
                    ->info('Default executor that will be used in "execute" calls. If not stated, first registered executor will be default one.')
                ->end()
                ->append(new TwigNodeDefinition())
            ->end();

        return $treeBuilder;
    }
}
