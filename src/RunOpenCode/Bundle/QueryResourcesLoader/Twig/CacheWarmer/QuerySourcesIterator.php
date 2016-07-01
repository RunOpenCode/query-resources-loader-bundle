<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Twig\CacheWarmer;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class QuerySourcesIterator
 *
 * Iterator for all query resources in bundles and in the application Resources/query directory.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\Twig
 */
class QuerySourcesIterator implements \IteratorAggregate
{
    private $kernel;
    private $rootDir;
    private $queries;
    private $paths;

    /**
     * @param KernelInterface $kernel  A KernelInterface instance
     * @param string          $rootDir The directory where global query sources can be stored
     * @param array           $paths   Additional Twig paths to warm
     */
    public function __construct(KernelInterface $kernel, $rootDir, array $paths = array())
    {
        $this->kernel = $kernel;
        $this->rootDir = $rootDir;
        $this->paths = $paths;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        if (null !== $this->queries) {
            return $this->queries;
        }

        $this->queries = $this->findQuerySourcesInDirectory($this->rootDir.'/Resources/query');
        foreach ($this->kernel->getBundles() as $bundle) {
            $name = $bundle->getName();
            if ('Bundle' === substr($name, -6)) {
                $name = substr($name, 0, -6);
            }

            $this->queries = array_merge(
                $this->queries,
                $this->findQuerySourcesInDirectory($bundle->getPath().'/Resources/query', $name),
                $this->findQuerySourcesInDirectory($this->rootDir.'/'.$bundle->getName().'/query', $name)
            );
        }

        foreach ($this->paths as $dir => $namespace) {
            $this->queries = array_merge($this->queries, $this->findQuerySourcesInDirectory($dir, $namespace));
        }

        return $this->queries = new \ArrayIterator(array_unique($this->queries));
    }

    /**
     * Find query sources in the given directory.
     *
     * @param string      $dir       The directory where to look for query sources
     * @param string|null $namespace The query source namespace
     *
     * @return array
     */
    private function findQuerySourcesInDirectory($dir, $namespace = null)
    {
        if (!is_dir($dir)) {
            return array();
        }

        $templates = array();
        foreach (Finder::create()->files()->followLinks()->in($dir) as $file) {
            $templates[] = (null !== $namespace ? '@'.$namespace.'/' : '').str_replace('\\', '/', $file->getRelativePathname());
        }

        return $templates;
    }
}
