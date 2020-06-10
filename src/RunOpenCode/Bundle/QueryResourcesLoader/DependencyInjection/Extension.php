<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection;

use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Configuration\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension as BaseExtension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\Resource\FileExistenceResource;
use Symfony\Component\DependencyInjection\Reference;

final class Extension extends BaseExtension
{
    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'runopencode_query_resources_loader';
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace(): string
    {
        return 'http://www.runopencode.com/xsd-schema/query-resources-loader-bundle';
    }

    /**
     * {@inheritdoc}
     */
    public function getXsdValidationBasePath(): string
    {
        return __DIR__ . '/../Resources/config/schema';
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $this->configureTwigGlobals($config, $container);
        $this->configureTwigEnvironment($config, $container);
        $this->configureTwigWarmUpCommand($config, $container);
        $this->configureTwigResourcePaths($config, $container);
        $this->configureTwigBundlePaths($config, $container);

        if (null !== $config['default_executor']) {
            $container->setParameter('runopencode.query_resources_loader.default_executor', $config['default_executor']);
        }

        if (isset($config['twig']['autoescape_service'], $config['twig']['autoescape_service_method'])) {
            $config['twig']['autoescape'] = [new Reference($config['twig']['autoescape_service']), $config['twig']['autoescape_service_method']];
        }

        unset($config['twig']['autoescape_service'], $config['twig']['autoescape_service_method'], $config['twig']['globals']);

        $container->getDefinition('runopencode.query_resources_loader.twig')->replaceArgument(1, $config['twig']);
    }

    private function configureTwigGlobals(array $config, ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('runopencode.query_resources_loader.twig')) {
            return;
        }

        if (empty($config['twig']['globals'])) {
            return;
        }

        $definition = $container->getDefinition('runopencode.query_resources_loader.twig');

        foreach ($config['twig']['globals'] as $key => $global) {

            if (isset($global['type']) && 'service' === $global['type']) {
                $definition->addMethodCall('addGlobal', [$key, new Reference($global['id'])]);
                continue;
            }

            $definition->addMethodCall('addGlobal', [$key, $global['value']]);
        }
    }

    private function configureTwigEnvironment(array $config, ContainerBuilder $container): void
    {
        $configurator = $container->getDefinition('runopencode.query_resources_loader.twig.configurator.environment');
        $configurator->replaceArgument(0, $config['twig']['date']['format']);
        $configurator->replaceArgument(1, $config['twig']['date']['interval_format']);
        $configurator->replaceArgument(2, $config['twig']['date']['timezone']);
        $configurator->replaceArgument(3, $config['twig']['number_format']['decimals']);
        $configurator->replaceArgument(4, $config['twig']['number_format']['decimal_point']);
        $configurator->replaceArgument(5, $config['twig']['number_format']['thousands_separator']);
    }

    private function configureTwigWarmUpCommand(array $config, ContainerBuilder $container): void
    {
        $container
            ->getDefinition('runopencode.query_resources_loader.twig.query_sources_iterator')
            ->replaceArgument(2, $config['twig']['paths']);

    }

    private function configureTwigResourcePaths(array $config, ContainerBuilder $container): void
    {
        $loader = $container->getDefinition('runopencode.query_resources_loader.twig.loader.filesystem');

        // register user-configured paths
        foreach ($config['twig']['paths'] as $path => $namespace) {
            if (!$namespace) {
                $loader->addMethodCall('addPath', [$path]);
                continue;
            }

            $loader->addMethodCall('addPath', [$path, $namespace]);
        }
    }

    private function configureTwigBundlePaths(array $config, ContainerBuilder $container): void
    {
        $loader      = $container->getDefinition('runopencode.query_resources_loader.twig.loader.filesystem');
        $addTwigPath = static function ($dir, $bundle) use ($loader) {

            $name = $bundle;

            if ('Bundle' === \substr($name, -6)) {
                $name = \substr($name, 0, -6);
            }

            $loader->addMethodCall('addPath', [$dir, $name]);
        };

        // register bundles as Twig namespaces
        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {

            $dir = $container->getParameter('kernel.root_dir') . '/Resources/' . $bundle . '/query';

            if (\is_dir($dir)) {
                $addTwigPath($dir, $bundle);
            }

            $container->addResource(new FileExistenceResource($dir));

            $reflection = new \ReflectionClass($class);
            $dir        = \dirname($reflection->getFileName()) . '/Resources/query';

            if (\is_dir($dir)) {
                $addTwigPath($dir, $bundle);
            }

            $container->addResource(new FileExistenceResource($dir));
        }
    }
}
