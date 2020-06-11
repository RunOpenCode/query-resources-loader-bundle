<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Twig\CacheWarmer;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Fixtures\Bundles\BarBundle\BarBundle;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Fixtures\Bundles\FooBundle\FooBundle;
use RunOpenCode\Bundle\QueryResourcesLoader\Twig\CacheWarmer\QuerySourcesIterator;
use Symfony\Component\HttpKernel\KernelInterface;

final class QuerySourcesIteratorTest extends TestCase
{
    /**
     * @test
     */
    public function itIterates(): void
    {
        $kernel = $this
            ->getMockBuilder(KernelInterface::class)
            ->getMock();

        $kernel
            ->method('getBundles')
            ->willReturn([
                new FooBundle(),
                new BarBundle(),
            ]);

        $iterator = new QuerySourcesIterator($kernel, __DIR__ . '/../../Fixtures/app', [
            __DIR__ . '/../../Fixtures/paths/path1' => 'custom-path-1',
            __DIR__ . '/../../Fixtures/paths/path2' => 'custom-path-2',
        ]);

        $templates = [];

        $iterator->getIterator(); // Force loading from internal array cache...

        foreach ($iterator as $template) {
            $templates[] = $template;
        }

        $this->assertEquals([
            'bundles/FooBundle/foo.sql',
            '@Foo/foo.sql',
            '@Bar/bar.sql',
            '@custom-path-1/query-in-path1.sql',
            '@custom-path-2/query-in-path2.sql',
        ], $templates);
    }
}
