# Query options

Beside providing query file and parameters for its execution, you might want to provide additional instructions on how
your query should be executed. For that purpose, you can use query options.

Query options is a value object which provides additional instructions for query execution, such as which executor
should be used, or if query should be cached or not. If you don't provide any query options, it is assumed that you want
to use default executor and that query should not be cached.

Base options class provided within this bundle is `RunOpenCode\Bundle\QueryResourcesLoader\Model\Options`, and defines
two basic and optional options, `executor` and `cache`. This class is intended to be extended and customized for your
needs. Derived class provided within this bundle is `RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalOptions`,
which provides additional option for Doctrine Dbal executor, `isolation`. This class is intended to be extended as well.

Neither class locks you to use only provided options, you can add as many arbitrary options as you need, either for your
custom executor or for your custom middleware.

```php
<?php

use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheIdentity;
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Options;

$options = Options::create()
    ->withExecutor('foo')
    ->withCache(CacheIdentity::create('bar'));
```

When using Doctrine Dbal executor, you may set transaction isolation level for your query execution.

```php
<?php

use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheIdentity;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalOptions;

$options = DbalOptions::create()
    ->withExecutor('foo')
    ->withCache(CacheIdentity::create('bar'))
    ->withReadUncommitted();
```

For arbitrary options, you can use `withOption` method with any of the mentioned classes.

```php
<?php

use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheIdentity;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalOptions;

$options = DbalOptions::create()
    ->withOption('foo', 'bar');
```

Of course, you may pass options as array as well:

```php
use Doctrine\DBAL\TransactionIsolationLevel;
use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheIdentity;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalOptions;

$options = DbalOptions::create([
    'executor' => 'foo',
    'cache' => CacheIdentity::create('bar'),
    'isolation' => TransactionIsolationLevel::READ_UNCOMMITTED,
    'foo' => 'bar'
]);
```

Options classes contains a lot of useful methods for creating instances, and for setting and getting options. For more
details, please refer to the source code of the classes.
