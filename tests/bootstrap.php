<?php

declare(strict_types=1);

use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Fixtures\Fixtures;
use RunOpenCode\Bundle\QueryResourcesLoader\Tests\Resources\App\TestKernel;
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
    (new Filesystem())->remove(__DIR__ . '/Resources/var/cache');

    $kernel = new TestKernel('test', false);

    $kernel->boot();

    try {
        /**
         * @phpstan-ignore-next-line
         */
        $kernel->getContainer()->get(Fixtures::class)->execute();
    } finally {
        $kernel->shutdown();
    }
}
