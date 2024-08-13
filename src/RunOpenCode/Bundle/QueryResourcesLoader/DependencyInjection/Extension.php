<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection;

use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Configuration\Configuration;
use RunOpenCode\Bundle\QueryResourcesLoader\Twig\CacheWarmer\QuerySourcesIterator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileExistenceResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension as BaseExtension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @psalm-suppress MoreSpecificImplementedParamType, UnnecessaryVarAnnotation
 *
 * @phpstan-type Config = array{
 *     default_executor: string,
 *     cache: array{
 *         pool: string|null,
 *         default_ttl: int|null,
 *     },
 *     twig: array{
 *         paths: array<string, string>,
 *         date: array{
 *             format: string,
 *             interval_format: string,
 *             timezone: string,
 *         },
 *         number_format: array{
 *             decimals: int,
 *             decimal_point: string,
 *             thousands_separator: string
 *         },
 *         autoescape_service?: string,
 *         autoescape_service_method?: string,
 *         globals?: array<string, array{
 *             type?: string,
 *             id: string,
 *             value: string,
 *         }>
 *     }
 * }
 */
final class Extension extends BaseExtension
{
    public const DEFAULT_EXECUTOR  = 'runopencode.query_resources_loader.default_executor';
    public const CACHE_POOL        = 'runopencode.query_resources_loader.cache.pool';
    public const CACHE_DEFAULT_TTL = 'runopencode.query_resources_loader.cache.default_ttl';

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
        $loader->load('cache.xml');
        $loader->load('executor.xml');
        $loader->load('legacy.xml');
        $loader->load('loader.xml');
        $loader->load('manager.xml');
        $loader->load('twig.xml');

        /** @var Config $configuration */
        $configuration = $this->processConfiguration(new Configuration(), $configs);

        // Set cache middleware configuration parameters.
        $container->setParameter(self::CACHE_POOL, $configuration['cache']['pool']);
        $container->setParameter(self::CACHE_DEFAULT_TTL, $configuration['cache']['default_ttl']);
        // Set name of the default executor.
        $container->setParameter(self::DEFAULT_EXECUTOR, $configuration['default_executor']);


        $this->configureTwigGlobals($configuration, $container);
        $this->configureTwigEnvironment($configuration, $container);
        $this->configureTwigWarmUpCommand($configuration, $container);
        $this->configureTwigResourcePaths($configuration, $container);
        $this->configureTwigBundlePaths($configuration, $container);
    }

    /**
     * @param Config $config
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
     * @param Config $config
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


        if (isset($config['twig']['autoescape_service'], $config['twig']['autoescape_service_method'])) {
            $config['twig']['autoescape'] = [new Reference($config['twig']['autoescape_service']), $config['twig']['autoescape_service_method']];
        }

        unset(
            $config['twig']['autoescape_service'],
            $config['twig']['autoescape_service_method'],
            $config['twig']['globals']
        );

        $container
            ->getDefinition('runopencode.query_resources_loader.twig')
            ->replaceArgument(1, $config['twig']);
    }

    /**
     * @param Config $config
     */
    private function configureTwigWarmUpCommand(array $config, ContainerBuilder $container): void
    {
        $container->getDefinition(QuerySourcesIterator::class)->replaceArgument(2, $config['twig']['paths']);
    }

    /**
     * @param Config $config
     */
    private function configureTwigResourcePaths(array $config, ContainerBuilder $container): void
    {
        /** @var string $projectDir */
        $projectDir = $container->getParameter('kernel.project_dir');
        $loader     = $container->getDefinition('runopencode.query_resources_loader.twig.loader.filesystem');

        // add "query" directory within project directory as default path, if exists
        $defaultPath = \sprintf('%s/query', $projectDir);

        if (\is_dir($defaultPath)) {
            $loader->addMethodCall('addPath', [$defaultPath, '__main__']);
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
     * @param Config $config
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

        $addTwigPath = static function(string $dir, string $bundle) use ($loader): void {
            $name = $bundle;

            if (\str_ends_with($name, 'Bundle')) {
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
