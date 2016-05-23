<?php
/*
 * This file is part of the QueryResourcesLoader Bundle, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader;

use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\ExecutorBuilderCompilerPass;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\RegisterExecutorsCompilerPass;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\TwigEnvironmentCompilerPass;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\TwigExtensionsCompilerPass;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\TwigLoaderCompilerPass;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class QueryResourcesLoaderBundle
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader
 */
class QueryResourcesLoaderBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new Extension();
    }

    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new TwigExtensionsCompilerPass())
            ->addCompilerPass(new TwigEnvironmentCompilerPass())
            ->addCompilerPass(new TwigLoaderCompilerPass())
            ->addCompilerPass(new ExecutorBuilderCompilerPass())
            ->addCompilerPass(new RegisterExecutorsCompilerPass())
        ;
    }
}
