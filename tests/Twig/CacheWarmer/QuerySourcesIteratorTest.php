<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Twig\CacheWarmer;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Resources\bundles\BarBundle\BarBundle;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Resources\bundles\FooBundle\FooBundle;
use RunOpenCode\Bundle\QueryResourcesLoader\Twig\CacheWarmer\QuerySourcesIterator;
use Symfony\Component\HttpKernel\KernelInterface;

final class QuerySourcesIteratorTest extends TestCase
{
    public function testItIterates(): void
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

        /**
         * @phpstan-ignore-next-line
         * @psalm-suppress InvalidArrayOffset
         */
        $iterator = new QuerySourcesIterator($kernel, \realpath(__DIR__ . '/../../Resources/App'), [
            \realpath(__DIR__ . '/../../Resources/paths/path1') => 'custom-path-1',
            \realpath(__DIR__ . '/../../Resources/paths/path2') => 'custom-path-2',
        ]);

        $templates = [];

        foreach ($iterator as $template) {
            $templates[] = $template;
        }

        $this->assertEquals([
            'get_all_from_default.sql.twig',
            'bar/count_from_bar.sql.twig',
            'bundles/FooBundle/foo.sql',
            'get_all_from_foo.sql.twig',
            '@Foo/foo.sql',
            '@Bar/bar.sql',
            '@custom-path-1/query-in-path1.sql',
            '@custom-path-2/query-in-path2.sql',
        ], $templates);
    }
}
