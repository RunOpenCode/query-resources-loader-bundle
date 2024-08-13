<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Legacy;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface;
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
final readonly class Executor implements ExecutorInterface
{
    public function __construct(
        private QueryResourcesLoaderInterface $loader,
        private ManagerInterface              $manager,
        private ?string                       $executor,
    ) {
        // noop
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $name, array $parameters = [], array $types = []): ExecutionResultInterface
    {
        return $this->loader->execute($name, Parameters::create($parameters, $types), Options::create([
            'executor' => $this->executor,
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function transactional(\Closure $scope, array $options = []): mixed
    {
        return $this->manager->transactional($scope, $options);
    }
}
