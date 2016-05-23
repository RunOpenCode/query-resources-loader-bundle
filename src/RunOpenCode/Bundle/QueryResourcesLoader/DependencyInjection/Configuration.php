<?php
/*
 * This file is part of the QueryResourcesLoader Bundle, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection;

use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Configuration\TwigConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('run_open_code_query_resources_loader');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('default_executor')
                    ->defaultValue(null)
                    ->info('Default executor that will be used in "execute" calls. If not stated, first registered executor will be default one.')
                ->end()
                ->append(TwigConfiguration::build())
            ->end()
            ;

        return $treeBuilder;
    }
}
