<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Twig\CacheWarmer;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Iterator for all query resources in bundles and in the application Resources/query directory.
 *
 * @implements \IteratorAggregate<string>
 */
final class QuerySourcesIterator implements \IteratorAggregate
{
    private KernelInterface $kernel;

    private string $projectDirectory;

    /**
     * @var string[]
     */
    private array $paths;

    /**
     * @var string[]
     */
    private array $queries;

    /**
     * @param KernelInterface $kernel           A KernelInterface instance
     * @param string          $projectDirectory The directory where global query sources can be stored
     * @param array<string>   $paths            Additional Twig paths to warm
     */
    public function __construct(KernelInterface $kernel, string $projectDirectory, array $paths = [])
    {
        $this->kernel           = $kernel;
        $this->projectDirectory = $projectDirectory;
        $this->paths            = $paths;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        if (!isset($this->queries)) {
            $this->queries = $this->findQuerySourcesInDirectory($this->projectDirectory . '/query');
            $bundles       = $this->kernel->getBundles();
            $bundleQueries = [];
            $pathQueries   = [];

            foreach ($bundles as $bundle) {
                $name = $bundle->getName();

                if ('Bundle' === substr($name, -6)) {
                    $name = substr($name, 0, -6);
                }

                $bundleQueries[] = $this->findQuerySourcesInDirectory($bundle->getPath() . '/Resources/query', $name);
                $bundleQueries[] = $this->findQuerySourcesInDirectory($this->projectDirectory . '/bundles/query/' . $bundle->getName());
            }

            foreach ($this->paths as $path) {
                $pathQueries[] = $this->findQuerySourcesInDirectory($path);
            }

            $this->queries = \array_merge($this->queries, ...$bundleQueries, ...$pathQueries);
        }

        return new \ArrayIterator($this->queries);
    }

    /**
     * Find query sources in the given directory.
     *
     * @param string      $dir       The directory where to look for query sources
     * @param string|null $namespace The query source namespace
     *
     * @return string[]
     */
    private function findQuerySourcesInDirectory(string $dir, ?string $namespace = null): array
    {
        if (!\is_dir($dir)) {
            return [];
        }

        $templates = [];

        /**
         * @var SplFileInfo[] $files
         */
        $files = Finder::create()->files()->followLinks()->in($dir);

        foreach ($files as $file) {
            $templates[] = (null !== $namespace ? '@' . $namespace . '/' : '') . str_replace('\\', '/', $file->getRelativePathname());
        }

        return $templates;
    }
}
