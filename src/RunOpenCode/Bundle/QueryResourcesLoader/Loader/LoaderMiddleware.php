<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Loader;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\LoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\MiddlewareInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\InvalidArgumentException;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;

final readonly class LoaderMiddleware implements MiddlewareInterface
{
    private LoaderInterface $default;

    /**
     * @var array<string, LoaderInterface>
     */
    private array $loaders;

    /**
     * @param iterable<string, LoaderInterface> $loaders
     */
    public function __construct(
        LoaderInterface $default,
        iterable        $loaders,
    ) {
        $loaders       = \is_array($loaders) ? $loaders : \iterator_to_array($loaders);
        $this->default = $default;
        $this->loaders = \array_merge($loaders, [
            'chained' => new ChainedLoader($loaders),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(string $query, Parameters $parameters, Options $options, callable $next): ExecutionResultInterface
    {
        $requested = $options->getLoader();
        $loader    = null === $requested ? $this->default : $this->loaders[$requested] ?? throw new InvalidArgumentException(\sprintf(
            'Requested loader "%s" not found.',
            $requested
        ));

        return $next(
            $loader->get($query, $parameters->getParameters()),
            $parameters,
            $options
        );
    }
}
