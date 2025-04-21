<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Loader;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\InvalidArgumentException;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\KernelTestCase;

final class IntegrationTest extends KernelTestCase
{
    private QueryResourcesLoaderInterface $loader;

    public function setUp(): void
    {
        parent::setUp();

        $this->loader = $this->getContainer()->get(QueryResourcesLoaderInterface::class); // @phpstan-ignore-line

        $this->createFixtures();
    }

    public function testItUsesDefaultLoader(): void
    {
        $count = $this->loader->execute('bar/count_from_bar.sql.twig')->getSingleScalarResult();

        $this->assertSame(5, $count);
    }

    public function testItUsesRawLoader(): void
    {
        $count = $this->loader->execute('SELECT COUNT(*) AS cnt FROM bar', null, Options::loader('raw'))->getSingleScalarResult();

        $this->assertSame(5, $count);
    }

    public function testItUsesChainedLoader(): void
    {
        $count = $this->loader->execute('SELECT COUNT(*) AS cnt FROM bar', null, Options::loader('chained'))->getSingleScalarResult();

        $this->assertSame(5, $count);
    }

    public function testItThrowsExceptionOnMissingLoader(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->loader->execute('SELECT COUNT(*) AS cnt FROM bar', null, Options::loader('foo'));
    }
}
