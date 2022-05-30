<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Loader;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\LoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SourceNotFoundException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SyntaxException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

final class TwigLoader implements LoaderInterface
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        return $this->twig->getLoader()->exists($name);
    }

    /**
     * {@inheritdoc}
     *
     * @throws SourceNotFoundException
     * @throws SyntaxException
     * @throws RuntimeException
     */
    public function get(string $name, array $args = []): string
    {
        try {
            return $this->twig->render($name, $args);
        } catch (LoaderError $e) {
            throw new SourceNotFoundException(\sprintf(
                'Could not find query source: "%s".',
                $name
            ), $e);
        } catch (SyntaxError $e) {
            throw new SyntaxException(\sprintf(
                'Query source "%s" contains Twig syntax error and could not be compiled.',
                $name
            ), $e);
        } catch (\Exception $e) {
            throw new RuntimeException('Unknown exception occurred', $e);
        }
    }
}
