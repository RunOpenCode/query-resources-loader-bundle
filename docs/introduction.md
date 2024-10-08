# Introduction

Reporting libraries/modules within typical PHP application (if stored procedures or views are not used) usual practice
is to write database queries within repository classes, or other class services.

Typical example of such implementation (pseudocode) would look like class below:

```php
declare(strict_types=1);

use Doctrine\DBAL\Connection;

final readonly class MyReportingRepository 
{
    public function __construct(private Connection $db) {}

    public function getInvoicingReportData(int $year): iterable
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
            
            T.year = :year
            
            [A lot of where statements and so on...]                                           
        ';
        
        $this->db->execute($sql, [ 
            'year' => $year 
        ]);            
    }
}
```

Having in mind example given above, here is a short list of identified issues with this kind of approach:

- Method size can easily go over 30 lines of code, which is against good coding practice.
- Consequently, class size can easily go over few hundred lines of code, also against good coding practice.
- RAD tools with some kind of SQL builder which have syntax checker, autocomplete, testing and executing playground is
  impossible to use while building a query statement.
- Mixing of query statements with application code seams wrong, almost like mixing HTML and PHP together.
- You cannot just send your code to DB expert to help you optimise/write some complex query if he is not familiar with
  PHP and Symfony, he will not be able to work on a query without your assistance.

Naturally, query code should residue in separated files and included in project with some kind of inclusion statement or
service call.

This bundle is created to solve those issues and allow you to separate your query statements from your application code.

## Proposed solution

Solution is inspired by Symfony's way of loading templates into a controller and rendering response.

Symfony proposes convention where template code is separated (as per MVC) from application logic and held in
`templates` (or `Resources/views`) directory. Required rendering logic can be coded in templates by using very powerful
templating language Twig. Rendering of templates is executed via dedicated service, while templates are identified via
path or bundle resource locator syntax(e.g. `@BundleName/template.html.twig`).

Inspired with that convention, this bundle proposes similar approach:

- Your queries are held in separate files in `query` directory of your application (or `Resources/query` directories for
  bundles, or any other directory per your configuration).
- Queries are files where only query (SQL code) should exist.
- Complex queries with some kind of query building logic can use Twig as pre-processing script language.
- You can use a dedicated service `RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface` to
  load query from its location and execute it.

### Example

In image below, a real-world example of directory structure of project reporting bundle which uses this bundle is
presented:

![Project structure with query files](img/file_structure.jpg "Real world example of this bundle usage")

**NOTE**: _This image is from project using Symfony 3, that is how old this bundle is, but it is still actively
maintained, battle tested and used in production for latest Symfony version._

In that matter, in order to execute the query stored within one of those files, following code can be used for data
source/repository class:

```php
declare(strict_types=1);

namespace App\Reporting\Repository;

use RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalParameters;

final readonly class ReportingDataSource
{
    public function __construct(private QueryResourcesLoaderInterface $loader) { }     

    public function getLedgerData(Criteria $criteria): iterable
    {
        return $this->loader->execute('common.ledger.sql', DbalParameters::create()
            ->dateTimeImmutable('from', $criteria->getFrom())
            ->dateTimeImmutable('to', $criteria->getTo())
            ->integer('account', $criteria->getAccount())
        );
    }
}
```

Note how your code gets cleaner by just omitting query statements from your PHP code.

Do note that Doctrine query language for fetching entities is not powerful as raw SQL. However, with this bundle, you
can easily leverage raw SQL queries to fetch identifiers of entities and then fetch entities with Doctrine.

