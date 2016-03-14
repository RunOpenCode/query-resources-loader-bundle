<?php

namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('run_open_code_sql_resources_loader');

        $rootNode
            ->children()
                ->scalarNode('manager')
                    ->defaultValue('static')
                    ->info('Manager which will be used as entry point for this service. Known implementations are: "static". You can pass your own manager service id.')
                ->end()
                ->append($this->getFilesystemLoaderConfiguration())
                ->append($this->getCachedLoaderConfiguration())
            ->end()
        ->end();

        return $treeBuilder;
    }

    protected function getFilesystemLoaderConfiguration()
    {
        $node = new ArrayNodeDefinition('filesystem');

        $node
            ->children()
                ->arrayNode('paths')
                    ->info('Custom paths where queries should be searched for.')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ->end();

        return $node;
    }

    protected function getCachedLoaderConfiguration()
    {
        $node = new ArrayNodeDefinition('cache');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('engine')
                    ->defaultValue('filesystem')
                    ->info('Caching engine for speeding up query loading. Known Doctrine caching engines are "filesystem", "apc" and "apcu" or you can pass your caching engine service. Set to "none" if caching engine should not be used.')
                ->end()
                ->booleanNode('disable_dev')
                    ->defaultTrue()
                    ->info('Whether caching should be disabled in development environment.')
                ->end()
            ->end()
        ->end();

        return $node;
    }
}
