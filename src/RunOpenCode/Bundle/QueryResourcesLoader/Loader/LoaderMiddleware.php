<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Loader;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\LoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\MiddlewareInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;

final readonly class LoaderMiddleware implements MiddlewareInterface
{
    public function __construct(private LoaderInterface $loader)
    {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(string $query, Parameters $parameters, Options $options, callable $next): ExecutionResultInterface
    {
        return $next(
            $this->loader->get($query, $parameters->getParameters()),
            $parameters,
            $options
        );
    }
}
