<?php

declare(strict_types=1);

use Symfony\Component\Filesystem\Filesystem;

require \dirname(__DIR__) . '/vendor/autoload.php';

/**
 * @psalm-suppress RiskyTruthyFalsyComparison
 */
$bootstrap = \getenv('BOOTSTRAP') ?: 'true';

// We will allow users to pass env variable and disable database rebuilding,
// application cache clearing, which can speed up testing process, especially
// when we are running only one test case.
if (\in_array(\strtolower($bootstrap), ['yes', '1', 'true'], true)) {
    // Clear the cache before running the tests.
    (new Filesystem())->remove(__DIR__ . '/Resources/App/var/cache');
}
