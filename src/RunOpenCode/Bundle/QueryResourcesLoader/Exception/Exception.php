<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Exception;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExceptionInterface;

/**
 * Base exception class for library.
 */
class Exception extends \Exception implements ExceptionInterface
{
    public function __construct(string $message, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
