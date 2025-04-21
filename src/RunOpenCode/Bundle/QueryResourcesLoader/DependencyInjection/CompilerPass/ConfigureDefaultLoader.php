<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\LoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Extension;
use RunOpenCode\Bundle\QueryResourcesLoader\Loader\RawLoader;
use RunOpenCode\Bundle\QueryResourcesLoader\Loader\TwigLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final readonly class ConfigureDefaultLoader implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter(Extension::DEFAULT_LOADER)) {
            return;
        }

        /** @var string $loader */
        $loader = $container->getParameter(Extension::DEFAULT_LOADER);

        match ($loader) {
            'twig' => $container->setAlias(LoaderInterface::class, TwigLoader::class),
            'raw' => $container->setAlias(LoaderInterface::class, RawLoader::class),
            default => $container->setAlias(LoaderInterface::class, $loader),
        };
    }
}
