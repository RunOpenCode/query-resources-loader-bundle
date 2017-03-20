<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Twig\CacheWarmer;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\DependencyInjection\Configuration\Fixtures\bundles\BarBundle\BarBundle;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\DependencyInjection\Configuration\Fixtures\bundles\FooBundle\FooBundle;
use RunOpenCode\Bundle\QueryResourcesLoader\Twig\CacheWarmer\QuerySourcesIterator;
use Symfony\Component\HttpKernel\KernelInterface;

class QuerySourcesIteratorTest extends TestCase
{
    /**
     * @test
     */
    public function itIterates()
    {
        $kernel = $this
            ->getMockBuilder(KernelInterface::class)
            ->getMock();

        $kernel
            ->method('getBundles')
            ->willReturn([
                new FooBundle(),
                new BarBundle()
            ]);

        $iterator = new QuerySourcesIterator($kernel, __DIR__.'/../../DependencyInjection/Configuration/Fixtures/app', array(
            __DIR__.'/../../DependencyInjection/Configuration/Fixtures/paths/path1' => 'custom-path-1',
            __DIR__.'/../../DependencyInjection/Configuration/Fixtures/paths/path2' => 'custom-path-2',
        ));

        $templates = array();

        $iterator->getIterator(); // Force loading from internal array cache...

        foreach ($iterator as $template) {
            $templates[] = $template;
        }

        $this->assertEquals(array(
            '@Foo/foo.sql',
            '@Bar/bar.sql',
            '@custom-path-1/query-in-path1.sql',
            '@custom-path-2/query-in-path2.sql'
        ), $templates);
    }
}
