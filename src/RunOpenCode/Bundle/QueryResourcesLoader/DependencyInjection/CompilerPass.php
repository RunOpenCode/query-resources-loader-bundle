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

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\FilesystemCache;
use RunOpenCode\Bundle\QueryResourcesLoader\Cache\DoctrineCacheProxy;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class CompilerPass
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection
 */
class CompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this
            ->processFilesystemLoader($container)
            ->processChainedLoader($container)
            ->processCachedLoader($container)
            ->processManager($container)
        ;
    }

    /**
     * @param ContainerBuilder $container
     * @return CompilerPass $this
     */
    protected function processFilesystemLoader(ContainerBuilder $container)
    {
        if ($container->hasDefinition('run_open_code.query_resources_loader.loader.filesystem_loader')) {

            $definition = $container->getDefinition('run_open_code.query_resources_loader.loader.filesystem_loader');

            $dir = $container->getParameter('kernel.root_dir').'/Resources/query';

            if (is_dir($dir)) {
                $definition->addMethodCall('addPath', array($dir));
            }

            foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {

                $reflection = new \ReflectionClass($class);
                $bundleName = ('Bundle' === substr($bundle, -6)) ? substr($bundle, 0, -6) : $bundle;

                $dir = $container->getParameter('kernel.root_dir').'/Resources/'.$bundle.'/query';

                if (is_dir($dir)) {
                    $definition->addMethodCall('addPath', array($dir, $bundleName));
                }

                $dir = dirname($reflection->getFileName()).'/Resources/query';

                if (is_dir($dir)) {
                    $definition->addMethodCall('addPath', array($dir, $bundleName));
                }
            }
        }

        return $this;
    }

    /**
     * @param ContainerBuilder $container
     * @return CompilerPass $this
     */
    protected function processChainedLoader(ContainerBuilder $container)
    {
        if ($container->hasDefinition('run_open_code.query_resources_loader.loader.chained_loader')) {

            $loaders = $container->findTaggedServiceIds('run_open_code.query_resources_loader.loader');

            switch (count($loaders)) {
                case 0:

                    throw new LogicException('At least one query loader must be provided.');

                    break;
                case 1:

                    foreach ($loaders as $id => $tags) {
                        $container->setAlias('run_open_code.query_resources_loader.loader', $id);
                    }

                    $container->removeDefinition('run_open_code.query_resources_loader.loader.chained_loader');

                    break;
                default:
                    $definition = $container->getDefinition('run_open_code.query_resources_loader.loader.chained_loader');

                    $prioritizedLoaders = array();

                    foreach ($loaders as $id => $tags) {

                        foreach ($tags as $attributes) {
                            $priority = !empty($attributes['priority']) ? $attributes['priority'] : 0;
                            $prioritizedLoaders[$priority][] = $id;
                        }
                    }

                    krsort($prioritizedLoaders);

                    foreach ($prioritizedLoaders as $loaders) {

                        foreach ($loaders as $loader) {

                            $definition->addMethodCall('addLoader', array(new Reference($loader)));
                        }
                    }

                    $container->setAlias('run_open_code.query_resources_loader.loader', 'run_open_code.query_resources_loader.loader.chained_loader');

                    break;
            }
        }

        return $this;
    }

    /**
     * @param ContainerBuilder $container
     * @return CompilerPass $this
     */
    protected function processCachedLoader(ContainerBuilder $container)
    {
        if ($container->hasDefinition('run_open_code.query_resources_loader.loader.cached_loader')) {

            $definition = $container->getDefinition('run_open_code.query_resources_loader.loader.cached_loader');
            $engine = $container->getParameter('run_open_code.query_resources_loader.loader.cached_loader.engine');

            if (method_exists($this, ($method = sprintf('buildDoctrine%sCacheEngineDefinition', ucfirst($engine))))) {

                /**
                 * @var Definition $cacheEngineDefinition
                 */
                $cacheEngineDefinition = $this->{$method}($container);

                $cacheEngineDefinition
                    ->addMethodCall('setNamespace', array('roc.query_resource_loader'))
                    ->setPublic(false)
                ;

                $container
                    ->setDefinition('run_open_code.query_resources_loader.loader.cached_loader.engine.doctrine_proxy_engine', $cacheEngineDefinition);

                $engineDefinition = new Definition(DoctrineCacheProxy::class, array(
                    new Reference('run_open_code.query_resources_loader.loader.cached_loader.engine.doctrine_proxy_engine')
                ));

                $container
                    ->setDefinition('run_open_code.query_resources_loader.loader.cached_loader.engine', $engineDefinition);

                $definition->setArguments(array(
                    new Reference($container->getAlias('run_open_code.query_resources_loader.loader')),
                    new Reference('run_open_code.query_resources_loader.loader.cached_loader.engine')
                ));

                $container->setAlias('run_open_code.query_resources_loader.loader', 'run_open_code.query_resources_loader.loader.cached_loader');

            } elseif ($container->hasDefinition($engine)) {

                $definition->setArguments(array(
                    new Reference($container->getAlias('run_open_code.query_resources_loader.loader')),
                    new Reference($engine)
                ));

                $container->setAlias('run_open_code.query_resources_loader.loader', 'run_open_code.query_resources_loader.loader.cached_loader');

            } else {
                throw new LogicException(sprintf('Unknown caching engine service provided: "%s".', $engine));
            }
        }

        return $this;
    }

    /**
     * @param ContainerBuilder $container
     * @return CompilerPass $this
     */
    protected function processManager(ContainerBuilder $container)
    {
        if ($managerId = $container->hasParameter('run_open_code.query_resources_loader.manager')) {

            if ($container->hasDefinition($managerId)) {

                $container->addAliases(array(
                    'run_open_code.query_resources_loader' => $managerId,
                    'roc.query_resources_loader' => $managerId,
                    'roc.query_loader' => $managerId
                ));
            } else {
                switch ($managerId) {
                    case 'static':

                        $container->addAliases(array(
                            'run_open_code.query_resources_loader' => 'run_open_code.query_resources_loader.manager.static',
                            'roc.query_resources_loader' => 'run_open_code.query_resources_loader.manager.static',
                            'roc.query_loader' => 'run_open_code.query_resources_loader.manager.static'
                        ));
                        break;
                    default:
                        throw new LogicException(sprintf('Unknown manager service provided: "%s".', $managerId));
                        break;
                }
            }
        }

        return $this;
    }

    protected function buildDoctrineFilesystemCacheEngineDefinition(ContainerBuilder $container)
    {
        return new Definition(FilesystemCache::class, array(
            $container->getParameter('kernel.cache_dir')
        ));
    }

    protected function buildDoctrineApcCacheEngineDefinition(ContainerBuilder $container)
    {
        return new Definition(ApcCache::class);
    }

    protected function buildDoctrineApcuCacheEngineDefinition(ContainerBuilder $container)
    {
        return new Definition(ApcuCache::class);
    }
}
