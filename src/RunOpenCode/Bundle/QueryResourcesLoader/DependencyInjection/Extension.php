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

/**
 * @psalm-suppress MoreSpecificImplementedParamType
 */
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
     * @param array<string, mixed> $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $this->configureTwigGlobals($config, $container); // @phpstan-ignore-line
        $this->configureTwigEnvironment($config, $container); // @phpstan-ignore-line
        $this->configureTwigWarmUpCommand($config, $container); // @phpstan-ignore-line
        $this->configureTwigResourcePaths($config, $container); // @phpstan-ignore-line
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

    /**
     * @param array{
     *      twig: array{
     *           globals?: array<string, array{
     *              type?: string,
     *              id: string,
     *              value: string,
     *           }>
     *      }
     * } $config
     */
    private function configureTwigGlobals(array $config, ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('runopencode.query_resources_loader.twig')) {
            return;
        }

        if (!isset($config['twig']['globals'])) {
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

    /**
     * @param array{
     *     twig: array{
     *         date: array{
     *              format: string,
     *              interval_format: string,
     *              timezone: string,
     *         },
     *         number_format: array{
     *              decimals: int,
     *              decimal_point: string,
     *              thousands_separator: string
     *         }
     *     }
     * } $config
     */
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

    /**
     * @param array{
     *     twig: array{
     *          paths: array<string, string>
     *     }
     * } $config
     */
    private function configureTwigWarmUpCommand(array $config, ContainerBuilder $container): void
    {
        $container
            ->getDefinition('runopencode.query_resources_loader.twig.query_sources_iterator')
            ->replaceArgument(2, $config['twig']['paths']);
    }

    /**
     * @param array{
     *     twig: array{
     *          paths: array<string, string>
     *     }
     * } $config
     */
    private function configureTwigResourcePaths(array $config, ContainerBuilder $container): void
    {
        /** @var string $projectDir */
        $projectDir = $container->getParameter('kernel.project_dir');
        $loader     = $container->getDefinition('runopencode.query_resources_loader.twig.loader.filesystem');

        // add "query" directory within project directory as default path, if exists
        $defaultPath = \sprintf('%s/query', $projectDir);

        if (\is_dir($defaultPath)) {
            $loader->addMethodCall('addPath', [$defaultPath]);
            $container->addResource(new FileExistenceResource($defaultPath));
            $container->setParameter('runopencode.query_resources_loader.default_path', $defaultPath);
        }

        // register user-configured paths
        foreach ($config['twig']['paths'] as $path => $namespace) {
            if (!$namespace) {
                $loader->addMethodCall('addPath', [$path]);
                continue;
            }

            $loader->addMethodCall('addPath', [$path, $namespace]);
        }
    }

    /**
     * @param array<string, mixed> $config
     *
     * @psalm-suppress UnusedParam
     */
    private function configureTwigBundlePaths(array $config, ContainerBuilder $container): void
    {
        /** @var array<string, class-string> $bundles */
        $bundles = $container->getParameter('kernel.bundles');
        $loader  = $container->getDefinition('runopencode.query_resources_loader.twig.loader.filesystem');
        /** @var string $projectDir */
        $projectDir = $container->getParameter('kernel.project_dir');

        $addTwigPath = static function (string $dir, string $bundle) use ($loader): void {
            $name = $bundle;

            if ('Bundle' === \substr($name, -6)) {
                $name = \substr($name, 0, -6);
            }

            $loader->addMethodCall('addPath', [$dir, $name]);
        };

        // Register bundles as Twig namespaces
        foreach ($bundles as $bundle => $class) {
            $dir = $projectDir . '/query/bundles/' . $bundle;

            if (\is_dir($dir)) {
                $addTwigPath($dir, $bundle);
            }

            $container->addResource(new FileExistenceResource($dir));

            $reflection = new \ReflectionClass($class);
            /** @var string $filename */
            $filename = $reflection->getFileName();
            $dir      = \dirname($filename) . '/Resources/query';

            if (\is_dir($dir)) {
                $addTwigPath($dir, $bundle);
            }

            $container->addResource(new FileExistenceResource($dir));
        }
    }
}
