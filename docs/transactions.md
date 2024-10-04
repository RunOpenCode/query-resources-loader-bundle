# Support for transactions

`RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface` defines a method `transactional()`
which accepts a callable as an argument. Callable should accept an instance of
`RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface` as an argument and return a value which
will be returned from `transactional()` method.

In general, `transactional()` method will start a transaction, execute your closure and commit transaction if closure
has been executed successfully. If closure throws an exception, transaction will be rolled back. This is compatible with
how Doctrine's `Connection::transactional()` method works.

However, there are some differences:

- `transactional()` method accepts, after callable, variadic number of instance of
  `RunOpenCode\Bundle\QueryResourcesLoader\Model\Options`. Options are used to define which executors should be used for
  creating transactional scope.
- If options are not provided, it is assumed that default executor should be used for creating transactional scope.
- Otherwise, with each passed option instance, you may define executors which need to be used for creating transactional
  scope. This is useful when you have multiple executors, and you want to execute statements from different executors
  within a single transaction scope (distributed transaction).

Since your callable will receive an instance of
`RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface` you may execute SQL statements within a
transaction scope as well as out of transaction scope, everything depends on passed options.

Example:

```php
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;

$loader->transactional(static function(QueryResourcesLoaderInterface $loader): iterable {
    $records = [
        ['id' => 1, 'name' => 'John'],
        ['id' => 2, 'name' => 'Jane'],
    ];

    foreach ($records as $record) {
        // inserts records into foo_connection, within distributed transaction scope
        $executor->execute('@foo/insert.sql', $record);
    }
    
    foreach ($records as $record) {
        // inserts records into bar_connection, within distributed transaction scope
        $executor->execute('@bar/insert.sql', $record);
    }
    
    // selects records from baz_connection, but not in transaction scope, since baz_connection is not part of distributed transaction
    return $executor->execute('@baz/select.sql', Options::executor('doctrine.dbal.baz_connection'));

}, Options::executor('doctrine.dbal.foo_connection'), Options::executor('doctrine.dbal.bar_connection'));
```

API is deliberately designed to be similar to `Doctrine\DBAL\Connection::transactional()` method where you are able to
take advantage of having SQL statements in separate files and still be able to execute them within a transaction scope.

Note that your SQL statements MUST NOT contain any transaction related statements, like `START TRANSACTION`, `COMMIT`,
etc... Library will take care of that for you. On top of that, if you
use [https://github.com/dmaicher/doctrine-test-bundle](https://github.com/dmaicher/doctrine-test-bundle) having
transaction statements in your SQL files will cause issues when rolling back database to initial state.

[DoctrineDbalExecutorResult](doctrine-dbal-executor) | [Table of contents](index.md) | [FAQ](faq.md)
