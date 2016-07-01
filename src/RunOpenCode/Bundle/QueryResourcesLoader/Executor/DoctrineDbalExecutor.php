<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Executor;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface;
use Doctrine\DBAL\Connection;

/**
 * Class DoctrineDbalExecutor
 *
 * Doctrine Dbal query executor.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\Executor
 */
class DoctrineDbalExecutor implements ExecutorInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($query, array $parameters = array())
    {
        return $this->connection->executeQuery($query, $parameters);
    }
}
