<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

/**
 * Interface for classes which should be aware of execution result.
 *
 * This interface is useful for classes that are some kind of configuration
 * of middleware or executor, and they should be aware of execution result
 * to be able to process it.
 *
 * It is used for cache middleware to be able to provide additional cache
 * tags for cache identity and/or to mutate TTL. Can not, nor it should be
 * used for modifying cache key.
 *
 * @template T of ExecutionResultAwareInterface
 */
interface ExecutionResultAwareInterface
{
    /**
     * Set execution result.
     *
     * This method should return new instance of class implementing
     * this interface, however, it is not required to be immutable.
     * For performance reasons, implementers may choose to mutate
     * the instance.
     *
     * @param ExecutionResultInterface $result
     *
     * @return T
     */
    public function withExecutionResult(ExecutionResultInterface $result): ExecutionResultAwareInterface;
}
