<?php
/*
 * This file is part of the QueryResourcesLoader Bundle, an RunOpenCode project.
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
 * Manager service provides SQL source code from loaders. Manager can modify source code, if needed, as per concrete
 * implementation of relevant manager.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\Manager
 */
interface ManagerInterface
{
    /**
     * Get SQL source by its name.
     *
     * @param string $name Name of SQL source code.
     * @param array $args Arguments for modification of SQL source code.
     * @return string SQL statement.
     */
    public function get($name, array $args = array());

    /**
     *  Check if manager have the SQL source code by its given name.
     *
     * @param string $name The name of the SQL source to check if can be loaded.
     * @return bool TRUE If the SQL source code is handled by this manager or not.
     */
    public function has($name);
}
