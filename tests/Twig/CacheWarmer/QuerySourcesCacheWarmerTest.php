<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Twig\CacheWarmer;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Twig\CacheWarmer\QuerySourcesCacheWarmer;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Template;
use Twig\TemplateWrapper;

final class QuerySourcesCacheWarmerTest extends TestCase
{
    /**
     * @test
     */
    public function itIsAlwaysOptional(): void
    {
        $twig = $this
            ->getMockBuilder(Environment::class)
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
    public function itWarmsUp(): void
    {
        $twig = $this
            ->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $template = $this
            ->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twig
            ->method('load')
            ->willReturn(new TemplateWrapper($twig, $template));

        $traversable = new \ArrayIterator(['template1', 'template2']);
        $warmer      = new QuerySourcesCacheWarmer($twig, $traversable);

        $warmer->warmUp(\sys_get_temp_dir());

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function itSilentlySkipsTwigErrorWhenWarmup(): void
    {
        $twig = $this
            ->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twig
            ->method('load')
            ->willThrowException(new Error('Template could not be loaded.'));

        $traversable = new \ArrayIterator(['template1', 'template2']);
        $warmer      = new QuerySourcesCacheWarmer($twig, $traversable);

        $warmer->warmUp(\sys_get_temp_dir());

        $this->assertTrue(true);
    }
}
