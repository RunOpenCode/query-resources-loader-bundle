<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * Bundle configuration tree.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('runopencode_query_resources_loader');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('default_executor')
                    ->defaultValue(null)
                    ->info('Default executor that will be used in "execute" calls. If not stated, first registered executor will be default one.')
                ->end()
                ->append(new TwigNodeDefinition())
            ->end()
            ;

        return $treeBuilder;
    }
}
