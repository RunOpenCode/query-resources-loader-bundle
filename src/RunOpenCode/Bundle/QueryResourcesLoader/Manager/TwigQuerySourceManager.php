<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Manager;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExceptionInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExecutionException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SourceNotFoundException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SyntaxException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

/**
 * Twig powered query executor.
 */
final class TwigQuerySourceManager implements ManagerInterface
{
    private Environment $twig;

    /**
     * @var array<string, ExecutorInterface>
     */
    private array $executors;

    /**
     * @param array<string, ExecutorInterface> $executors
     */
    public function __construct(Environment $twig, iterable $executors = [])
    {
        $this->twig      = $twig;
        $this->executors = [];

        foreach ($executors as $name => $executor) {
            $this->registerExecutor($executor, $name);
        }
    }

    /**
     * Register query executor.
     *
     * @param ExecutorInterface $executor
     * @param string            $name
     */
    public function registerExecutor(ExecutorInterface $executor, string $name): void
    {
        $this->executors[$name] = $executor;
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
     * @throws RuntimeException
     * @throws ExecutionException
     */
    public function execute(string $name, array $args = [], array $types = [], array $options = [], ?string $executor = null): ExecutionResultInterface
    {
        if (null === $executor) {
            $executor = (string)\array_key_first($this->executors);
        }

        if (!\array_key_exists($executor, $this->executors)) {
            throw new RuntimeException(sprintf('Requested executor "%s" does not exists.', $executor));
        }

        /** @var ExecutorInterface $executorInstance */
        $executorInstance = $this->executors[$executor];

        try {
            return $executorInstance->execute($this->get($name, $args), $args, $types, $options);
        } catch (\Exception $exception) {
            if ($exception instanceof ExceptionInterface) {
                throw $exception;
            }

            throw new ExecutionException(\sprintf(
                'Query "%s" could not be executed.',
                $name
            ), $exception);
        }
    }
}
