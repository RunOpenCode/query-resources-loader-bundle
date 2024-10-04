Caching
=======

Sometimes, execution of your SQL query can be expensive. Even if execution of SQL query is fast, fetching of data from
database is something you want to avoid if you can, knowing that data will not change in a while. After all, database is
something that is not easy to scale, and you want to keep it as fast as possible.

In that matter, you would like to cache your query results, and that is quite easy with this bundle.

## Configuration

Library depends on `symfony/cache` component, so you need to have it installed in your project and configured. By
default, library will use `cache.app` pool from Symfony's default cache configuration, but you can configure your own
cache pool. Read more about cache pool configuration [here](https://symfony.com/doc/current/cache.html).

Default cache configuration is given below:

```yaml
# config/packages/query_resources_loader.yaml

runopencode_query_resources_loader:
    cache_pool: cache.app
    default_ttl: ~  # Default TTL for cache items in seconds, if not provided, cache item will not expire.
```

See configuration reference for more details [here](installation.md).

## Usage

For each query execution which you want to cache, you need to provide a cache identity. Cache identity is defined with
`RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentityInterface`. Library provides default implementation of
interface `RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheIdentity` which you may use out-of-the-box.

In example below, we will cache ledger report for accounting purposes.

```php
<?php

declare(strict_types=1);

namespace App\Reporting\Repository;

use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheIdentity;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalOptions;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalParameters;

final readonly class ReportingRepository
{
    public function __construct(private QueryResourcesLoaderInterface $loader) { }

    public function getLedgerData(LedgerCritera $criteria): iterable
    {
        $key = \sprintf(
            'accounting_ledger_report_%s_%s_%s',
            $criteria->getAccount(),
            $criteria->getFrom()->format('Y-m-d'),
            $criteria->getTo()->format('Y-m-d'),        
        );
    
        return $this->loader->execute('common.ledger.sql', 
            DbalParameters::create()
                ->integer('account', $criteria->getAccount())
                ->dateImmutable('from', $criteria->getFrom())
                ->dateImmutable('to', $criteria->getTo()),
            DbalOptions::cached(new CacheIdentity($key, ['accounting', 'ledger'], 3600)),
        ));
    }
}
```

Cache key in our example is constructed from account number and date range. Tags are used to invalidate cache for all
accounting reports. Cache will expire in 1 hour.

You might notice that our example repository uses repository criteria pattern. If you are not familiar with it, you can
find more about it from various sources on the Internet. One of the articles worth considering
is [https://www.beberlei.de/post/doctrine_repositories](https://www.beberlei.de/post/doctrine_repositories).

If you are using repository criteria pattern, you may move your cache key generation logic to criteria object. For that
purpose, you need to implement `RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentifiableInterface`. Next
example demonstrates such approach.

```php
<?php

declare(strict_types=1);

namespace App\Reporting\Repository;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\CacheIdentifiableInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheIdentity;

final class LedgerCriteria implements CacheIdentifiableInterface 
{
    public function __construct(
        private int $account,
        private \DateTimeImmutable $from, 
        private \DateTimeImmutable $to
    ) { 
        // noop
    }
    
    public function getAccount(): int
    {
        return $this->account;
    }
    
    public function getFrom(): \DateTimeImmutable
    {
        return $this->from;
    }
    
    public function getTo(): \DateTimeImmutable
    {
        return $this->to;
    }
    
    public function getCacheIdentity(): CacheIdentity
    {
        return new CacheIdentity(
            \sprintf(
                'accounting_ledger_report_%s_%s_%s',
                $this->account, 
                $this->from->format('Y-m-d'), 
                $this->to->format('Y-m-d')
            ),
            ['accounting', 'ledger'],
            3600
        );
    }
}
```

Now, repository from initial example becomes much simpler:

```php
<?php

declare(strict_types=1);

namespace App\Reporting\Repository;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalOptions;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalParameters;

final readonly class ReportingRepository
{
    public function __construct(private QueryResourcesLoaderInterface $loader) { }

    public function getLedgerData(LedgerCritera $criteria): iterable
    {    
        return $this->loader->execute('common.ledger.sql', 
            DbalParameters::create()
                ->integer('account', $criteria->getAccount())
                ->dateImmutable('from', $criteria->getFrom())
                ->dateImmutable('to', $criteria->getTo()),
            DbalOptions::cached($criteria),
        ));
    }
}
```

Which approach you will choose is matter of your personal preference. Both approaches are valid and will work as
expected.
