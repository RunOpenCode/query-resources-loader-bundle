<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Fixtures;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

final readonly class FooFixtures
{
    public function __construct(
        private Connection $connection
    ) {
        // noop
    }

    public function execute(): void
    {
        $this->connection->executeQuery('DROP TABLE IF EXISTS foo;');

        $schema = new Schema();

        $myTable = $schema->createTable('foo');
        $myTable->addColumn('id', 'integer', ['unsigned' => true]);
        $myTable->addColumn('title', 'string', ['length' => 32]);
        $myTable->addColumn('description', 'string', ['length' => 255]);
        $myTable->setPrimaryKey(['id']);

        $this->connection->executeQuery($schema->toSql($this->connection->getDatabasePlatform())[0]);

        $records = [
            ['id' => 1, 'title' => 'Foo title 1', 'description' => 'Foo description 1'],
            ['id' => 2, 'title' => 'Foo title 2', 'description' => 'Foo description 2'],
            ['id' => 3, 'title' => 'Foo title 3', 'description' => 'Foo description 3'],
            ['id' => 4, 'title' => 'Foo title 4', 'description' => 'Foo description 4'],
            ['id' => 5, 'title' => 'Foo title 5', 'description' => 'Foo description 5'],
        ];

        foreach ($records as $record) {
            $this->connection->executeQuery('INSERT INTO foo (id, title, description) VALUES (:id, :title, :description);', $record);
        }
    }
}
