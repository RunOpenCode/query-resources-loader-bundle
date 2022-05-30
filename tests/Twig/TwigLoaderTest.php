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
    /**
     * @test
     */
    public function itHasQuery(): void
    {
        $this->assertTrue($this->getTwigLoader()->has('@test/query-1'));
    }

    /**
     * @test
     */
    public function itDoesNotHaveQuery(): void
    {
        $this->assertFalse($this->getTwigLoader()->has('unknown'));
    }

    /**
     * @test
     */
    public function itGetsQuery(): void
    {
        $this->assertSame('THIS IS SIMPLE, PLAIN QUERY', $this->getTwigLoader()->get('@test/query-1'));
        $this->assertSame('THIS IS SIMPLE, PLAIN QUERY WITH VARIABLE X', $this->getTwigLoader()->get('@test/query-2', ['var' => 'X']));
    }

    /**
     * @test
     */
    public function itThrowsSyntaxError(): void
    {
        $this->expectException(SyntaxException::class);
        $this->getTwigLoader()->get('@test/syntax-error');
    }

    /**
     * @test
     */
    public function itThrowsNotFoundException(): void
    {
        $this->expectException(SourceNotFoundException::class);
        $this->getTwigLoader()->get('not-existing');
    }

    /**
     * @test
     */
    public function itThrowsUnknownException(): void
    {
        $twig = $this
            ->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twig
            ->method('render')
            ->willThrowException(new \Exception());

        $loader = $this->getTwigLoader();

        $this->expectException(RuntimeException::class);

        $loader->get('does_not_exists');
    }

    private function getTwigLoader(): TwigLoader
    {
        return new TwigLoader(new Environment(new ArrayLoader([
            '@test/query-1'      => 'THIS IS SIMPLE, PLAIN QUERY',
            '@test/query-2'      => 'THIS IS SIMPLE, PLAIN QUERY WITH VARIABLE {{var}}',
            '@test/syntax-error' => 'THIS IS SIMPLE, PLAIN QUERY WITH TWIG SYNTAX ERROR {% if x',
        ])));
    }
}