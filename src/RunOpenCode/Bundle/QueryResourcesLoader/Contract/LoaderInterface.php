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

use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SourceNotFoundException;

/**
 * Interface LoaderInterface
 *
 * Loader loads SQL source code from source repository/storage.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\Contract
 */
interface LoaderInterface
{
    /**
     * Get SQL source code by its name.
     *
     * @param string $name Sql source name.
     * @return string Sql source code.
     *
     * @throws SourceNotFoundException
     */
    public function load($name);

    /**
     * Check if loader have the SQL source code by its given name.
     *
     * @param string $name The name of the SQL source to check if can be loaded.
     * @return bool TRUE If the SQL source code is handled by this loader or not.
     */
    public function exists($name);
}
