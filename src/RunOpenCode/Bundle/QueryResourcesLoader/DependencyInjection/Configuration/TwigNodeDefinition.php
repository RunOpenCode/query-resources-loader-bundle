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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Class TwigNodeDefinition
 *
 * Twig environment configuration.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Configuration
 * @internal
 */
class TwigNodeDefinition extends ArrayNodeDefinition
{
    public function __construct()
    {
        parent::__construct('twig');

        $this
            ->configureTwigOptions()
            ->configureTwigFormatOptions()
            ->addDefaultsIfNotSet()
            ->configureTwigGlobals()
        ;
    }

    /**
     * Configure Twig options.
     *
     * @return TwigNodeDefinition $this
     */
    private function configureTwigOptions()
    {
        $this
            ->fixXmlConfig('path')
            ->children()
                ->variableNode('autoescape')->defaultValue(false)->end()
                ->scalarNode('autoescape_service')->defaultNull()->end()
                ->scalarNode('autoescape_service_method')->defaultNull()->end()
                ->scalarNode('base_template_class')->example('Twig_Template')->cannotBeEmpty()->end()
                ->scalarNode('cache')->defaultValue('%kernel.cache_dir%/query_resources/twig')->end()
                ->scalarNode('charset')->defaultValue('%kernel.charset%')->end()
                ->booleanNode('debug')->defaultValue('%kernel.debug%')->end()
                ->booleanNode('strict_variables')->end()
                ->scalarNode('auto_reload')->end()
                ->integerNode('optimizations')->min(-1)->end()
                ->arrayNode('paths')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('paths')
                    ->beforeNormalization()
                    ->always()
                    ->then(function ($paths) {
                        $normalized = array();
                        foreach ($paths as $path => $namespace) {
                            if (is_array($namespace)) {
                                // xml
                                $path = $namespace['value'];
                                $namespace = $namespace['namespace'];
                            }

                            // path within the default namespace
                            if (ctype_digit((string) $path)) {
                                $path = $namespace;
                                $namespace = null;
                            }

                            $normalized[$path] = $namespace;
                        }

                        return $normalized;
                    })
                    ->end()
                    ->prototype('variable')->end()
                ->end()
            ->end()
        ;

        return $this;
    }

    /**
     * Configure Twig format options.
     *
     * @return TwigNodeDefinition $this
     */
    private function configureTwigFormatOptions()
    {
        $this
            ->children()
                ->arrayNode('date')
                    ->info('The default format options used by the date filter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('format')->defaultValue('F j, Y H:i')->end()
                        ->scalarNode('interval_format')->defaultValue('%d days')->end()
                        ->scalarNode('timezone')
                            ->info('The timezone used when formatting dates, when set to null, the timezone returned by date_default_timezone_get() is used')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('number_format')
                    ->info('The default format options for the number_format filter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('decimals')->defaultValue(0)->end()
                        ->scalarNode('decimal_point')->defaultValue('.')->end()
                        ->scalarNode('thousands_separator')->defaultValue(',')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $this;
    }

    /**
     * Configure Twig globals.
     *
     * @return TwigNodeDefinition $this
     */
    private function configureTwigGlobals()
    {
        $this
            ->fixXmlConfig('global')
            ->children()
                ->arrayNode('globals')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('key')
                    ->example(array('foo' => '"@bar"', 'pi' => 3.14))
                    ->prototype('array')
                        ->beforeNormalization()
                            ->ifTrue(function ($v) { return is_string($v) && 0 === strpos($v, '@'); })
                            ->then(function ($v) {
                                if (0 === strpos($v, '@@')) {
                                    return substr($v, 1);
                                }

                                return array('id' => substr($v, 1), 'type' => 'service');
                            })
                        ->end()
                        ->beforeNormalization()
                            ->ifTrue(function ($v) {
                                if (is_array($v)) {
                                    $keys = array_keys($v);
                                    sort($keys);

                                    return $keys !== array('id', 'type') && $keys !== array('value');
                                }

                                return true;
                            })
                            ->then(function ($v) { return array('value' => $v); })
                        ->end()
                        ->children()
                            ->scalarNode('id')->end()
                            ->scalarNode('type')
                                ->validate()
                                    ->ifNotInArray(array('service'))
                                    ->thenInvalid('The %s type is not supported')
                                ->end()
                            ->end()
                            ->variableNode('value')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $this;
    }
}
