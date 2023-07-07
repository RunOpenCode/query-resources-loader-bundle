<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Contract;

/**
 * Iteration result.
 *
 * @extends \Traversable<mixed, mixed>
 *
 * @deprecated Use https://github.com/ReactiveX/RxPHP for buffering and batching results.
 */
interface IterateResultInterface extends \Traversable
{
    /**
     * Configuration value for type of iteration when row should be yielded.
     */
    public const ITERATE_ROW = 'iterate_row';

    /**
     * Configuration value for type of iteration when only first column should be yielded.
     */
    public const ITERATE_COLUMN = 'iterate_column';
}
