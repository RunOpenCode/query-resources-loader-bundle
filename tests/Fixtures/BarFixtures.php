<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\QueryResourcesLoader\Tests\Fixtures;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

final readonly class BarFixtures
{
    public function __construct(
        private Connection $connection
    ) {
        // noop
    }

    public function execute(): void
    {
        $this->connection->executeQuery('DROP TABLE IF EXISTS bar;');

        $schema = new Schema();

        $myTable = $schema->createTable('bar');
        $myTable->addColumn('id', 'integer', ['unsigned' => true]);
        $myTable->addColumn('title', 'string', ['length' => 32]);
        $myTable->addColumn('description', 'string', ['length' => 255]);
        $myTable->setPrimaryKey(['id']);

        $this->connection->executeQuery($schema->toSql($this->connection->getDatabasePlatform())[0]);

        $records = [
            ['id' => 1, 'title' => 'Bar title 1', 'description' => 'Bar description 1'],
            ['id' => 2, 'title' => 'Bar title 2', 'description' => 'Bar description 2'],
            ['id' => 3, 'title' => 'Bar title 3', 'description' => 'Bar description 3'],
            ['id' => 4, 'title' => 'Bar title 4', 'description' => 'Bar description 4'],
            ['id' => 5, 'title' => 'Bar title 5', 'description' => 'Bar description 5'],
        ];

        foreach ($records as $record) {
            $this->connection->executeQuery('INSERT INTO bar (id, title, description) VALUES (:id, :title, :description);', $record);
        }
    }
}
