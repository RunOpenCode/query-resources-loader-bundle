<?php

namespace RunOpenCode\Bundle\QueryResourcesLoader\Manager;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\LoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface;

/**
 * Class StaticSourceManager
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\Manager
 */
class StaticSourceManager implements ManagerInterface
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, array $args = array())
    {
        if (count($args) > 0) {
            throw new \RuntimeException('Static SQL source manager can not modify source code based on provided arguments.');
        }

        return $this->loader->load($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->loader->exists($name);
    }
}
