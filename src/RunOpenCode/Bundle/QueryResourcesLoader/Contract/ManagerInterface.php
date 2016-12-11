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
 * Interface ManagerInterface
 *
 * Manager service provides Query source code from loaders, modifying it, if needed, as per concrete implementation of
 * relevant manager and supported scripting language. Manager can execute a Query as well.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\Manager
 */
interface ManagerInterface
{
    /**
     * Get Query source by its name.
     *
     * @param string $name Name of Query source code.
     * @param array $args Arguments for modification/compilation of Query source code.
     * @return string SQL statement.
     */
    public function get($name, array $args = array());

    /**
     * Execute Query source.
     *
     * @param string $name Name of Query source code.
     * @param array $args Arguments for modification/compilation of Query source code, as well as params for query statement.
     * @param array $types Types of parameters for prepared statement.
     * @param null|string $executor Executor name.
     * @return mixed Execution results.
     */
    public function execute($name, array $args = array(), array $types = array(), $executor = 'default');

    /**
     * Check if manager have the Query source code by its given name.
     *
     * @param string $name The name of the Query source to check if can be loaded.
     * @return bool TRUE If the Query source code is handled by this manager or not.
     */
    public function has($name);
}
