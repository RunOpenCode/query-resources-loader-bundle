<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Exception;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExceptionInterface;

class LogicException extends \LogicException implements ExceptionInterface
{
}
