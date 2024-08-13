<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Fixtures;

final readonly class Fixtures
{
    public function __construct(
        private FooFixtures $foo,
        private BarFixtures $bar
    ) {
        // noop
    }

    public function execute(): void
    {
        $this->foo->execute();
        $this->bar->execute();
    }
}
