<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection;

use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Configuration\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension as BaseExtension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\Resource\FileExistenceResource;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class Extension
 *
 * Bundle extension.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection
 */
class Extension extends BaseExtension
{
    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'runopencode_query_resources_loader';
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return 'http://www.runopencode.com/xsd-schema/query-resources-loader-bundle';
    }

    /**
     * {@inheritdoc}
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this
            ->configureTwigGlobals($config, $container)
            ->configureTwigEnvironment($config, $container)
            ->configureTwigWarmUpCommand($config, $container)
            ->configureTwigResourcePaths($config, $container)
            ->configureTwigBundlePaths($config, $container)
            ;

        if (null !== $config['default_executor']) {
            $container->setParameter('runopencode.query_resources_loader.default_executor', $config['default_executor']);
        }

        if (isset($config['twig']['autoescape_service'], $config['twig']['autoescape_service_method'])) {
            $config['twig']['autoescape'] = array(new Reference($config['twig']['autoescape_service']), $config['twig']['autoescape_service_method']);
        }

        unset($config['twig']['autoescape_service'], $config['twig']['autoescape_service_method'], $config['twig']['globals']);

        $container->getDefinition('runopencode.query_resources_loader.twig')->replaceArgument(1, $config['twig']);
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @return Extension $this
     */
    protected function configureTwigGlobals(array $config, ContainerBuilder $container)
    {
        if (false !== $container->hasDefinition('runopencode.query_resources_loader.twig') && !empty($config['twig']['globals'])) {

            $definition = $container->getDefinition('runopencode.query_resources_loader.twig');

            foreach ($config['twig']['globals'] as $key => $global) {

                if (isset($global['type']) && 'service' === $global['type']) {
                    $definition->addMethodCall('addGlobal', array($key, new Reference($global['id'])));
                } else {
                    $definition->addMethodCall('addGlobal', array($key, $global['value']));
                }
            }
        }

        return $this;
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @return Extension $this
     */
    protected function configureTwigEnvironment(array $config, ContainerBuilder $container)
    {
        $configurator = $container->getDefinition('runopencode.query_resources_loader.twig.configurator.environment');
        $configurator->replaceArgument(0, $config['twig']['date']['format']);
        $configurator->replaceArgument(1, $config['twig']['date']['interval_format']);
        $configurator->replaceArgument(2, $config['twig']['date']['timezone']);
        $configurator->replaceArgument(3, $config['twig']['number_format']['decimals']);
        $configurator->replaceArgument(4, $config['twig']['number_format']['decimal_point']);
        $configurator->replaceArgument(5, $config['twig']['number_format']['thousands_separator']);

        return $this;
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @return Extension $this
     */
    protected function configureTwigWarmUpCommand(array $config, ContainerBuilder $container)
    {
        $container->getDefinition('runopencode.query_resources_loader.twig.query_sources_iterator')->replaceArgument(2, $config['twig']['paths']);

        return $this;
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @return Extension $this
     */
    protected function configureTwigResourcePaths(array $config, ContainerBuilder $container)
    {
        $twigFilesystemLoaderDefinition = $container->getDefinition('runopencode.query_resources_loader.twig.loader.filesystem');

        // register user-configured paths
        foreach ($config['twig']['paths'] as $path => $namespace) {
            if (!$namespace) {
                $twigFilesystemLoaderDefinition->addMethodCall('addPath', array($path));
            } else {
                $twigFilesystemLoaderDefinition->addMethodCall('addPath', array($path, $namespace));
            }
        }

        return $this;
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @return Extension $this
     */
    protected function configureTwigBundlePaths(array $config, ContainerBuilder $container)
    {
        $twigFilesystemLoaderDefinition = $container->getDefinition('runopencode.query_resources_loader.twig.loader.filesystem');

        $addTwigPath = function($dir, $bundle) use ($twigFilesystemLoaderDefinition) {

            $name = $bundle;

            if ('Bundle' === substr($name, -6)) {
                $name = substr($name, 0, -6);
            }

            $twigFilesystemLoaderDefinition->addMethodCall('addPath', array($dir, $name));
        };

        // register bundles as Twig namespaces
        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {

            $dir = $container->getParameter('kernel.root_dir').'/Resources/'.$bundle.'/query';

            if (is_dir($dir)) {
                $addTwigPath($dir, $bundle);
            }

            $container->addResource(new FileExistenceResource($dir));

            $reflection = new \ReflectionClass($class);
            $dir = dirname($reflection->getFileName()).'/Resources/query';

            if (is_dir($dir)) {
                $addTwigPath($dir, $bundle);
            }

            $container->addResource(new FileExistenceResource($dir));
        }

        return $this;
    }
}
