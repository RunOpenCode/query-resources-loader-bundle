<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\ExecutorBuilderCompilerPass;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\RegisterExecutorsCompilerPass;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\TwigEnvironmentCompilerPass;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\TwigExtensionsCompilerPass;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\CompilerPass\TwigLoaderCompilerPass;
use RunOpenCode\Bundle\QueryResourcesLoader\DependencyInjection\Extension;
use RunOpenCode\Bundle\QueryResourcesLoader\QueryResourcesLoaderBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class QueryResourcesLoaderBundleTest extends TestCase
{
    /**
     * @test
     */
    public function itHasExtension()
    {
        $this->assertInstanceOf(Extension::class, $this->getBundle()->getContainerExtension());
    }

    /**
     * @test
     */
    public function itRegistersCompilerPasses()
    {
        $bundle = $this->getBundle();
        $compiler = new ContainerBuilder();
        $bundle->build($compiler);

        /**
         * @var PassConfig $passConfig
         */
        $passConfig = $compiler->getCompiler()->getPassConfig();

        $passes = array_filter(array_map(function (CompilerPassInterface $compilerPass) {
            $class = get_class($compilerPass);

            if (0 === strpos($class, 'RunOpenCode')) {
                return $class;
            }

            return null;
        }, $passConfig->getBeforeOptimizationPasses()));

        $expected = [
            TwigExtensionsCompilerPass::class,
            TwigEnvironmentCompilerPass::class,
            TwigLoaderCompilerPass::class,
            ExecutorBuilderCompilerPass::class,
            RegisterExecutorsCompilerPass::class,
        ];

        sort($passes);
        sort($expected);

        $this->assertEquals($expected, $passes);
    }

    private function getBundle()
    {
        return new QueryResourcesLoaderBundle();
    }
}
