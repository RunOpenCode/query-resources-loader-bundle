Query Resources Loader Bundle
=============================

[![Packagist](https://img.shields.io/packagist/v/RunOpenCode/query-resources-loader-bundle.svg)](https://packagist.org/packages/runopencode/query-resources-loader-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/RunOpenCode/query-resources-loader-bundle/?branch=master)

The purpose of query resources loader is to help you manage and organize your big, long, database queries, especially in
application that deals with reporting.

# Features:

- Store your queries in separate, `*.sql` files, in your project directory or any other directory that you want to use.
- Load or execute your queries using `runopencode.query_loader`
  (or `RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface`) service.
- **Full compatibility with Doctrine DBAL**. You can move your current queries within repository classes to separate SQL
  files and use query loader to execute them. Result of execution is instance of `Doctrine\DBAL\Driver\Result`. Of
  course, there are neat methods which you can utilize to fetch data from result set, such
  as `getSingleScalarResult()`, `getSingleResult()`, `getScalarResult()`, etc...
  See `RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutionResult` class for more details.
- Automatically registers `%kernel.project_dir%/query` directory as query resources directory, as well as all `query`
  directories within `Resources` directories of your bundles.
- Integrated with Twig, so you can use Twig syntax in your queries. You can use this feature to build complex queries,
  depending on your application logic. Beside control flow statements, you can use all Twig filters, functions, tests
  and blocks as well. With `{% include %}`, `{% embed %}`, `{% use %}` and `{% extends %}` statements, you can reuse
  your queries and build complex queries from smaller ones.
- Supports `transactional()` API from Doctrine DBAL, so you can execute your queries within transaction. Transactional
  even allows you to control transaction isolation level for current transaction, directly, from method execution.

Read the documentation [here](docs/index.md).

# Quick example

Typical reporting repository that has a query within repository can be implemented like as in example below:

```php
declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

final class MyReportingRepository 
{
    private Connection $connection;
    
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

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

With this bundle, you can store your queries in `%kernel.project_dir%/query` directory as stander `.sql` file (or any
other extension that your query language uses) and load it and execute it using `runopencode.query_loader` service,
thus, decreasing amount of code in your repository classes. Of course, service injection is highly encouraged:

```php
declare(strict_types=1);

namespace App\Repository;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface;
use Doctrine\DBAL\Types\Types;

final class MyReportingRepository 
{
    private ManagerInterface $manager;

    public function __construct(ManagerInterface $manager)
    {            
        $this->manager = $manager;
    }
    
    public function getInvoicingReport(\DateTimeInterface $from): iterable
    {
        return $this->manager->execute('invoicing_report.sql', [
            'from' => $from 
        ], [
            'from' => Types::DATE_IMMUTABLE   
        ]);      
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

- Remove `RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface::iterate()` method in version 8.0,
  depreciate support for PHP 7+.