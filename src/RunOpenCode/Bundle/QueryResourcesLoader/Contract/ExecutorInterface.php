<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

/**
 * Interface ExecutorInterface
 *
 * Executor executes query in native environment.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\Contract
 */
interface ExecutorInterface
{
    /**
     * Execute query.
     *
     * @param string $query Query to execute.
     * @param array $parameters Parameters required for query.
     * @return mixed Result of execution.
     */
    public function execute($query, array $parameters = array());
}
