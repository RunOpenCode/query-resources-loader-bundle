<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Legacy;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalOptions;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;

/**
 * Compatibility layer for older versions.
 *
 * This is a compatibility layer for older versions of this library which allows
 * to use new version of library with old interfaces.
 *
 * @deprecated
 */
final readonly class Manager implements ManagerInterface
{
    public function __construct(
        private QueryResourcesLoaderInterface $loader,
    ) {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $name, array $parameters = [], array $types = [], ?string $executor = null): ExecutionResultInterface
    {
        return $this->loader->execute($name, Parameters::create($parameters, $types), Options::create([
            'executor' => $executor,
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function transactional(\Closure $scope, array $options = [], ?string $executor = null)
    {
        /**
         * @psalm-suppress InvalidArgument
         */
        $options = isset($options['isolation'])
            ?
            DbalOptions::create([
                'isolation' => $options['isolation'],
                'executor'  => $executor,
            ])
            : Options::create([
                'executor' => $executor,
            ]);
        $self    = $this;

        return $this->loader->transactional(static function(QueryResourcesLoaderInterface $loader) use ($scope, $executor, $self): mixed {
            return $scope(new Executor($loader, $self, $executor));
        }, $options);
    }
}
