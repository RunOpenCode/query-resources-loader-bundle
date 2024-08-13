Query resources loader bundle
=============================

[![Packagist](https://img.shields.io/packagist/v/RunOpenCode/query-resources-loader-bundle.svg)](https://packagist.org/packages/runopencode/query-resources-loader-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/?branch=master)

The purpose of query resources loader is to help you manage and organize your big, long, database queries, especially in
application that deals with reporting.

# Features:

- Store your queries in separate, `*.sql` files (or `*sql.twig` files), in your project directory or any other directory
  that you want to use.
- Load or execute your queries using `RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface`
  service.
- **Full compatibility with Doctrine Dbal**. You can move your current queries within repository classes to separate SQL
  files and use query loader to execute them. Result of execution is instance of `Doctrine\DBAL\Driver\Result`. Of
  course, there are neat methods which you can utilize to fetch data from result set, such
  as `getSingleScalarResult()`, `getSingleResult()`, `getScalarResult()`, etc...
  See `RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DoctrineDbalExecutionResult` class for more details.
- Automatically registers `%kernel.project_dir%/query` directory as query resources directory, as well as all `query`
  directories within `Resources` directories of your bundles.
- **Integrated with Twig**, so you can use Twig syntax in your queries. You can use this feature to build complex
  queries, depending on your application logic. Beside control flow statements, you can use all Twig filters, functions,
  tests and blocks as well. With `{% include %}`, `{% embed %}`, `{% use %}` and `{% extends %}` statements, you can
  reuse your queries and build complex queries from smaller ones.
- **Transactions**. You can execute your queries within transaction. Supports `transactional()` API from Doctrine Dbal.
  You can control transaction isolation level for current statements within transaction.
- **Distributed transactions**. You can execute multiple queries within same transaction against different databases. If
- **Caching**. You can cache your query results, so they are not loaded from database on each execution.

Read the documentation [here](docs/index.md).

# Quick example

Typical reporting repository that has a query string within repository can be implemented like as in example below:

```php
declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

final readonly class MyReportingRepository 
{
    public function __construct(private Connection $connection) { }

    public function getInvoicingReport(\DateTimeInterface $from): iterable
    {
        $sql = 'SELECT 
            
            field_1.T as f1,
            field_2.T as f2,
            
            ...
            
            field_57.X as f57,
            
            ...
            
            field_n.N as fn
            
            FROM 
            
            table_name T
            
            INNER JOIN table_name_2 T2 ON (T.id = T2.t1_id)
            
            INNER JOIN table_name_3 T3 ON (T2.id = T3.t2_id)
            
            ....
            
            [More joins]
            
            WHERE
            
            T.create_at >= :from
            
            [A lot of where statements and so on...]                                           
        ';
        
        return $this->connection->execute($sql, [ 
            'from' => $from 
        ], [
            'from' => Types::DATE_IMMUTABLE        
        ]);            
    }
}
```

**This is terrible, as it mixes SQL with PHP code, and it is hard to maintain!**

With this bundle, you can store your queries in `%kernel.project_dir%/query` directory as standard `.sql` file (or
`.sql.twig` if you use Twig, or any other extension that your query language uses) and load it and execute it using
`RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface` service, thus, decreasing amount of
code in your repository classes:

```php
declare(strict_types=1);

namespace App\Repository;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalParameters;

final readonly class MyReportingRepository 
{
    public function __construct(private QueryResourcesLoaderInterface $loader) { }                 
    
    public function getInvoicingReport(\DateTimeInterface $from): iterable
    {
        return $this->loader->execute('invoicing_report.sql', DbalParameters::create()->dateTimeImmutable('from', $from));      
    }
}
```

## Building complex queries

Sometimes, you will need a possibility to build up your queries depending on your application logic. For that purpose,
query loader uses Twig and all your query resources are pre-parsed with Twig, allowing you to dynamically build your
queries, per example:

```sql
# file: my_query.sql.twig
SELECT *
FROM my_table T

WHERE T.field_1 = :some_parameter {% if some_other_parameter is defined %}

    AND T.field_2 = :some_other_parameter 
    
{% endif %}
```

For other details about this bundle, as well as for tips on how to use it, read the documentation [here](docs/index.md).

## TODO

- Add profiling for middlewares and query execution.
- Add changelog.
- Improve documentation.
