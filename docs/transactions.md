# Support for transactions

`RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface` defines a
method `transactional(\Closure(ExecutorInterface $executor): T $scope, array $options = []): T` which accepts a closure
where you can execute your statements within a transaction scope. Returned value from your closure will be returned
from `transactional()` method.

Example:

```php
$result = $executor->transactional(static function(ExecutorInterface $executor): iterable {
    $records = [
        ['id' => 1, 'name' => 'John'],
        ['id' => 2, 'name' => 'Jane'],
    ];

    foreach ($records as $record) {
        $executor->execute('@App/insert.sql', $record);
    }
    
    return $executor->execute('@App/select.sql');
});
```

API is deliberately designed to be similar to `Doctrine\DBAL\Connection::transactional()` method where you are able to
take advantage of having SQL statements in separate files and still be able to execute them within a transaction scope.

Note that your SQL statements MUST NOT contain any transaction related statements, like `BEGIN TRANSACTION`, `COMMIT`,
etc...

[DoctrineDbalExecutorResult](doctrine-dbal-executor-result.md) | [Table of contents](index.md) | [FAQ](faq.md)
