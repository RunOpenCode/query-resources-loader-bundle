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
     */
    public function get(string $name, array $args = []): string
    {
        try {
            return $this->twig->render($name, $args);
        } catch (LoaderError $exception) {
            throw new SourceNotFoundException(\sprintf(
                'Could not find query source "%s".',
                $name
            ), $exception);
        } catch (SyntaxError $exception) {
            throw new SyntaxException(\sprintf(
                'Query source "%s" contains Twig syntax error and could not be compiled.',
                $name
            ), $exception);
        } catch (\Exception $exception) {
            throw new RuntimeException('Unknown exception occurred', $exception);
        }
    }
}
