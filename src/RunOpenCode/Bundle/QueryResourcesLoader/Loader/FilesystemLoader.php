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
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SourceLoaderException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Class FilesystemLoader
 *
 * Filesystem loader loads SQL source from files on local filesystem.
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\Loader
 */
class FilesystemLoader implements LoaderInterface
{
    const DEFAULT_NAMESPACE = '__default__';

    /**
     * @var array
     */
    private $paths;

    /**
     * @var array
     */
    private $cache;

    public function __construct(array $paths = array(), $namespace = self::DEFAULT_NAMESPACE)
    {
        $this->paths = array();
        $this->cache = array();

        $this->setPaths($paths, $namespace);
    }

    /**
     * {@inheritdoc}
     */
    public function load($name)
    {
        return file_get_contents($this->findSource($name));
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        $name = $this->normalizeName($name);

        if (isset($this->cache[$name])) {
            return true;
        }

        try {
            return false !== $this->findSource($name);
        } catch (ResourceNotFoundException $exception) {
            return false;
        }
    }

    public function setPaths(array $paths, $namespace = self::DEFAULT_NAMESPACE)
    {
        $this->paths[$namespace] = array();

        foreach ($paths as $path) {
            $this->addPath($path, $namespace);
        }
    }

    public function addPath($path, $namespace = self::DEFAULT_NAMESPACE)
    {
        $this->cache = array();

        if (!is_dir($path)) {
            throw new SourceLoaderException(sprintf('The directory "%s" does not exist.', $path));
        }

        $this->paths[$namespace][] = rtrim($path, '/\\');
    }

    public function prependPath($path, $namespace = self::DEFAULT_NAMESPACE)
    {
        $this->cache = array();

        if (!is_dir($path)) {
            throw new SourceLoaderException(sprintf('The directory "%s" does not exist.', $path));
        }

        $path = rtrim($path, '/\\');

        if (!isset($this->paths[$namespace])) {
            $this->paths[$namespace][] = $path;
        } else {
            array_unshift($this->paths[$namespace], $path);
        }
    }

    private function findSource($name)
    {
        $name = $this->normalizeName($name);

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $this->validateName($name);

        list($namespace, $shortname) = $this->parseName($name);

        if (!isset($this->paths[$namespace])) {
            throw new ResourceNotFoundException(sprintf('There are no registered paths for namespace "%s".', $namespace));
        }

        foreach ($this->paths[$namespace] as $path) {

            if (is_file($path.'/'.$shortname)) {

                if (false !== $realpath = realpath($path.'/'.$shortname)) {
                    return $this->cache[$name] = $realpath;
                }

                return $this->cache[$name] = $path.'/'.$shortname;
            }
        }

        throw new ResourceNotFoundException(sprintf('Unable to find source "%s" (looked into: %s).', $name, implode(', ', $this->paths[$namespace])));
    }

    private function normalizeName($name)
    {
        return preg_replace('#/{2,}#', '/', str_replace('\\', '/', $name));
    }

    private function validateName($name)
    {
        if (false !== strpos($name, "\0")) {
            throw new SourceLoaderException('SQL source code name cannot contain NULL bytes.');
        }

        $name = ltrim($name, '/');
        $parts = explode('/', $name);
        $level = 0;

        foreach ($parts as $part) {
            if ('..' === $part) {
                --$level;
            } elseif ('.' !== $part) {
                ++$level;
            }

            if ($level < 0) {
                throw new SourceLoaderException(sprintf('Looks like you try to load a SQL source outside configured directories (%s).', $name));
            }
        }
    }

    private function parseName($name, $default = self::DEFAULT_NAMESPACE)
    {
        if (isset($name[0]) && '@' === $name[0]) {
            if (false === $pos = strpos($name, '/')) {
                throw new SourceLoaderException(sprintf('Malformed namespaced SQL source name "%s" (expecting "@namespace/sql_source_name").', $name));
            }

            $namespace = substr($name, 1, $pos - 1);
            $shortname = substr($name, $pos + 1);

            return array($namespace, $shortname);
        }

        return array($default, $name);
    }
}
