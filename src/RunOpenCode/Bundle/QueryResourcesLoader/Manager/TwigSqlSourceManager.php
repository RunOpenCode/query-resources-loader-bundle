<?php
/*
 * This file is part of the QueryResourcesLoaderBundle, an RunOpenCode project.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\Bundle\QueryResourcesLoader\Manager;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExceptionInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\ExecutionException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\RuntimeException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SourceNotFoundException;
use RunOpenCode\Bundle\QueryResourcesLoader\Exception\SyntaxException;

/**
 * Class TwigSqlSourceManager
 *
 * @package RunOpenCode\Bundle\QueryResourcesLoader\Manager
 */
class TwigSqlSourceManager implements ManagerInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var ExecutorInterface[]
     */
    protected $executors;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
        $this->executors = array();
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, array $args = array())
    {
        try {
            return $this->twig->render($name, $args);
        } catch (\Twig_Error_Loader $e) {
            throw new SourceNotFoundException(sprintf('Could not find query source: "%s".', $name), 0, $e);
        } catch (\Twig_Error_Syntax $e) {
            throw new SyntaxException(sprintf('Query source "%s" contains Twig syntax error and could not be compiled.', $name), 0, $e);
        } catch (\Exception $e) {
            throw new RuntimeException('Unknown exception occured', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->twig->getLoader()->exists($name);
    }

    /**
     * {@inheritdoc}
     */
    public function execute($name, array $args = array(), $executor = 'default')
    {
        if (!array_key_exists($executor, $this->executors)) {
            throw  new RuntimeException(sprintf('Requested executor "%s" does not exists.', $executor));
        }

        $executor = $this->executors[$executor];

        try {
            /**
             * @var ExecutorInterface $executor
             */
            return $executor->execute($this->get($name, $args), $args);
        } catch (\Exception $e) {

            if ($e instanceof ExceptionInterface) {
                throw $e;
            }

            throw new ExecutionException(sprintf('Query "%s" could not be executed.', $name), 0, $e);
        }
    }

    /**
     * Register query executor.
     *
     * @param ExecutorInterface $executor
     * @param string $name
     */
    public function registerExecutor(ExecutorInterface $executor, $name)
    {
        $this->executors[$name] = $executor;
    }
}
