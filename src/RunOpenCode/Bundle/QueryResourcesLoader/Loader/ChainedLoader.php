<?php
/*
 * This file is part of the QueryResourcesLoader Bundle, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Loader;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\LoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SourceNotFoundException;

/**
 * Class ChainedLoader
 *
 * Chain loader allows you to keep SQL source in several different repositories.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\Loader
 */
class ChainedLoader implements LoaderInterface
{
    /**
     * @var array
     */
    private $loaders;

    public function __construct(array $loaders)
    {
        $this->setLoaders($loaders);
    }

    /**
     * {@inheritdoc}
     */
    public function load($name)
    {
        /**
         * @var LoaderInterface $loader
         */
        foreach ($this->loaders as $loader) {

            if ($loader->exists($name)) {
                return $loader->load($name);
            }
        }

        throw new SourceNotFoundException(sprintf('Unable to find source "%s".'. $name));
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        /**
         * @var LoaderInterface $loader
         */
        foreach ($this->loaders as $loader) {

            if ($loader->exists($name)) {
                return true;
            }
        }

        return false;
    }

    public function setLoaders($loaders)
    {
        $this->loaders = array();

        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    public function prependLoader(LoaderInterface $loader)
    {
        array_unshift($this->loaders, $loader);
    }
}