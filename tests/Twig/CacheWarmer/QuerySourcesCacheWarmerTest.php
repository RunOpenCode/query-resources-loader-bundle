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
use RunOpenCode\Bundle\QueryResourcesLoader\Twig\CacheWarmer\QuerySourcesCacheWarmer;

class QuerySourcesCacheWarmerTest extends TestCase
{
    /**
     * @test
     */
    public function itIsAlwaysOptional()
    {
        $twig = $this
            ->getMockBuilder(\Twig_Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $traversable = $this
            ->getMockBuilder(\Traversable::class)
            ->getMock();

        $warmer = new QuerySourcesCacheWarmer($twig, $traversable);
        $this->assertTrue($warmer->isOptional());
    }

    /**
     * @test
     */
    public function itWarmsUp()
    {
        $twig = $this
            ->getMockBuilder(\Twig_Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twig
            ->method('load')
            ->willReturn(null);

        $traversable = new \ArrayIterator(['template1', 'template2']);

        $warmer = new QuerySourcesCacheWarmer($twig, $traversable);

        $warmer->warmUp(sys_get_temp_dir());

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function itSilentlySkipsTwigErrorWhenWarmup()
    {
        $twig = $this
            ->getMockBuilder(\Twig_Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twig
            ->method('load')
            ->willThrowException(new \Twig_Error('Template could not be loaded.'));

        $traversable = new \ArrayIterator(['template1', 'template2']);

        $warmer = new QuerySourcesCacheWarmer($twig, $traversable);

        $warmer->warmUp(sys_get_temp_dir());

        $this->assertTrue(true);
    }
}
