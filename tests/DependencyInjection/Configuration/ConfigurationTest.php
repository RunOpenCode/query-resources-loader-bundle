<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\DependencyInjection\Configuration;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionConfigurationTestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Configuration\Configuration;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Extension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class ConfigurationTest extends AbstractExtensionConfigurationTestCase
{
    /**
     * @test
     */
    public function itHasReasonableDefaults(): void
    {
        $this->assertProcessedConfigurationEquals([
            'default_executor' => null,
            'twig'             => [
                'autoescape'                => false,
                'autoescape_service'        => null,
                'autoescape_service_method' => null,
                'cache'                     => '%kernel.cache_dir%/query_resources/twig',
                'charset'                   => '%kernel.charset%',
                'debug'                     => '%kernel.debug%',
                'paths'                     => [],
                'date'                      => [
                    'format'          => 'F j, Y H:i',
                    'interval_format' => '%d days',
                    'timezone'        => null,
                ],
                'number_format'             => [
                    'decimals'            => 0,
                    'decimal_point'       => '.',
                    'thousands_separator' => ',',
                ],
                'globals'                   => [],
            ],
        ], [
            __DIR__ . '/../../Fixtures/config/empty.xml',
        ]);
    }

    /**
     * @test
     */
    public function itCanBeProperlyConfigured(): void
    {
        $this->assertProcessedConfigurationEquals([
            'default_executor' => 'some.default.executor',
            'twig'             => [
                'autoescape'                => 'autoescape',
                'autoescape_service'        => 'autoescape-service',
                'autoescape_service_method' => 'autoescape-service-method',
                'base_template_class'       => 'base-template-class',
                'cache'                     => 'cache',
                'charset'                   => 'charset',
                'debug'                     => true,
                'paths'                     => [
                    '/First/Path'  => 'first/namespace',
                    '/Second/Path' => 'second/namespace',
                ],
                'date'                      => [
                    'format'          => 'Y-m-d',
                    'interval_format' => '%d',
                    'timezone'        => 'UTC',
                ],
                'number_format'             => [
                    'decimals'            => 2,
                    'decimal_point'       => ',',
                    'thousands_separator' => '.',
                ],
                'strict_variables'          => true,
                'optimizations'             => 1,
                'globals'                   => [
                    'some_key_1' => ['value' => 'Some value 1'],
                    'some_key_2' => ['value' => 'Some value 2'],
                    'some_key_3' => ['type' => 'service', 'id' => 'service_id'],
                ],
            ],
        ], [
            __DIR__ . '/../../Fixtures/config/full.xml',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtension(): ExtensionInterface
    {
        return new Extension();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
