<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Twig;

use PHPUnit\Framework\TestCase;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SourceNotFoundException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SyntaxException;
use RunOpenCode\Bundle\QueryResourcesLoader\Loader\TwigLoader;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class TwigLoaderTest extends TestCase
{
    private TwigLoader $loader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loader = new TwigLoader(new Environment(new ArrayLoader([
            '@test/query-1'      => 'THIS IS SIMPLE, PLAIN QUERY',
            '@test/query-2'      => 'THIS IS SIMPLE, PLAIN QUERY WITH VARIABLE {{var}}',
            '@test/syntax-error' => 'THIS IS SIMPLE, PLAIN QUERY WITH TWIG SYNTAX ERROR {% if x',
        ])));
    }


    public function testItHasQuery(): void
    {
        $this->assertTrue($this->loader->has('@test/query-1'));
    }

    public function testItDoesNotHaveQuery(): void
    {
        $this->assertFalse($this->loader->has('unknown'));
    }

    public function testItGetsQuery(): void
    {
        $this->assertSame('THIS IS SIMPLE, PLAIN QUERY', $this->loader->get('@test/query-1'));
        $this->assertSame('THIS IS SIMPLE, PLAIN QUERY WITH VARIABLE X', $this->loader->get('@test/query-2', ['var' => 'X']));
    }

    public function testItThrowsSyntaxError(): void
    {
        $this->expectException(SyntaxException::class);
        $this->loader->get('@test/syntax-error');
    }

    public function testItThrowsNotFoundException(): void
    {
        $this->expectException(SourceNotFoundException::class);
        $this->loader->get('not-existing');
    }

    public function testItThrowsUnknownException(): void
    {
        $twig = $this
            ->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twig
            ->method('render')
            ->willThrowException(new \Exception());

        $loader = $this->loader;

        $this->expectException(RuntimeException::class);

        $loader->get('does_not_exists');
    }
}
