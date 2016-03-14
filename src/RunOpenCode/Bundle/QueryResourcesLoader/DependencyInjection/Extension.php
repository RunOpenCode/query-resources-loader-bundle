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

use Doctrine\Common\Cache\FilesystemCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension as BaseExtension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class Extension extends BaseExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this
            ->processFilesystemLoaderConfiguration($container, $config)
            ->processCachingEngineConfiguration($container, $config)
            ->processManagerConfiguration($container, $config)
        ;
    }

    public function getAlias()
    {
        return "run_open_code_sql_resources_loader";
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     * @return Extension $this
     */
    protected function processFilesystemLoaderConfiguration(ContainerBuilder $container, array $config)
    {
        if (
            !empty($config['filesystem'])
            &&
            !empty($config['filesystem']['paths'])
            &&
            $container->hasDefinition('run_open_code.query_resources_loader.loader.filesystem_loader')
        ) {

            $definition = $container->getDefinition('run_open_code.query_resources_loader.loader.filesystem_loader');

            foreach ($config['filesystem']['paths'] as $namespace => $path) {

                if ($namespace) {
                    $definition->addMethodCall('addPath', array($path, $namespace));
                } else {
                    $definition->addMethodCall('addPath', array($path));
                }
            }

        }

        return $this;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     * @return Extension $this
     */
    protected function processCachingEngineConfiguration(ContainerBuilder $container, array $config)
    {
        if (
            ($container->getParameter('kernel.environment') === 'dev' && true === $config['cache']['disable_dev'])
            ||
            'none' === $config['cache']['engine']
        ) {
            $container->removeDefinition('run_open_code.query_resources_loader.loader.cached_loader');
        } else {

            $container->setParameter('run_open_code.query_resources_loader.loader.cached_loader.engine', $config['cache']['engine']);

        }

        return $this;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     * @return Extension $this
     */
    protected function processManagerConfiguration(ContainerBuilder $container, array $config)
    {
        $container->setParameter('run_open_code.query_resources_loader.manager', $config['manager']);

        return $this;
    }
}
