# Using manager

Manager defines 3 public methods:

    /**
     * Manager service provides Query source code from loaders, modifying it, if needed, as per concrete implementation of
     * relevant manager and supported scripting language. Manager can execute a Query as well.
     */
    interface ManagerInterface
    {
        /**
         * Check if manager have the Query source code by its given name.
         *
         * @param string $name The name of the Query source to check if can be loaded.
         *
         * @return bool TRUE If the Query source code is handled by this manager or not.
         */
        public function has(string $name): bool;

        /**
         * Get Query source by its name.
         *
         * @param string               $name Name of Query source code.
         * @param array<string, mixed> $args Arguments for modification/compilation of Query source code.
         *
         * @return string SQL statement.
         */
        public function get(string $name, array $args = []): string;
    
        /**
         * Execute Query source.
         *
         * @param string                $name     Name of Query source code.
         * @param array<string, mixed>  $args     Arguments for modification/compilation of Query source code, as well as params for query statement.
         * @param array<string, string> $types    Types of parameters for prepared statement.
         * @param array<string, mixed>  $options  Any executor specific options (depending on concrete driver).
         * @param null|string           $executor Executor name.
         *
         * @return ExecutionResultInterface<mixed, mixed> Execution results.
         */
        public function execute(string $name, array $args = [], array $types = [], array $options = [], ?string $executor = null): ExecutionResultInterface;

        /**
         * Execute query and iterate results in batches.
         *
         * Query is modified in order to accommodate LIMIT/OFFSET clauses,
         * provided query must not contain mentioned statements. Purpose is to
         * iterate rows without using table/database cursor and achieving small 
         * memory footprint on both application and database side.
         *
         * Options may contain additional keys, depending on concrete driver,
         * but all contains the following:
         *
         * - iterate: string, how values should be yielded for each row.
         * - batch_size: int, how many rows to process per query.
         * - on_batch_end: callable, callable to invoke when batch is fully processed.
         *
         * Executor may provide for prepared statement "last_batch_row" with last row
         * of previous batch which may be used for building of query for next batch.
         *
         * @param string                                                                                $query      Query to execute.
         * @param array<string, mixed>                                                                  $parameters Parameters required for query.
         * @param array<string, string>                                                                 $types      Parameter types required for query.
         * @param array<string, mixed>|array{iterate?:string, batch_size?:int, on_batch_end?: callable} $options    Any executor specific options (depending on concrete driver).
         *
         * @return IterateResultInterface<mixed, mixed> Result of execution.
         *
         * @see \RunOpenCode\Bundle\QueryResourcesLoader\Contract\IterateResultInterface::ITERATE_*
         */
        public function iterate(string $name, array $args = [], array $types = [], array $options = [], ?string $executor = null): IterateResultInterface;
    }

    
While first parameter `$name` is bundle resource locator syntax of query 
which you are trying to load/execute, second parameter allows you to use
parametrised query statements (prepared statements), as well as building
complex queries depending on passed arguments (see [Twig support](twig-support.md)).

Third, optional, parameter `$types` allows you to explicitly specify type
of parameters for prepared statements, which is useful when using, per example,
`WHERE IN` in your SQL statements.

## Query executor

Method `get()` will just provide you with loaded and parsed query string,
while method `execute()` will execute the query and provide you with the
query result as instance of 
`RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface`.

Note that used Manager implementation is not database agnostic, nor it 
is intended to be. Library provides you, for now, with 
`RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutor` 
which can be used for executing queries against relational databases
supported by Doctrine Dbal.

When executing SQL statements with `DoctrineDbalExecutor` (Dbal), result set is
returned as instance of
`RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutorResult`
class, which is a proxy to a `Doctrine\DBAL\Driver\Statement`.

You can, of course, implement your own query executor, by implementing
`RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface` interface
and registering it in service container with tag name 
`runopencode.query_resources_loader.executor` with attribute `name`
which you can use when executing query in multi-executor environment.
 
Default executor is the first registered executor, or, it can be
configured under `runopencode_query_resources_loader.default_executor`
key where you should state the service name of your executor, example:

    runopencode_query_resources_loader:
        default_executor: my_executor_service_name

## How to use manager

It is strongly advised to inject manager into your repository class/service
as in example in a previous chapter, however, you can get service from service
container as well:

    $this->get('runopencode.query_loader')->execute('@MyAppReporting/common.ledger.sql', array(
        'year' => '2016',
        'limit' => '10000'
    ));
    
While in `MyAppReportingBundle/Resources/query/common.ledger.sql` there can
be a query that uses, per example, prepared statement:
    
    SELECT * FROM expenses_table ET
    
    WHERE
    
    ET.year = :year
    
    AND
    
    ET.budget <= :limit;
    
        
By using Twig within this bundle, you can do a really serious and complex
query building as well.        

## Iterate

Consider that you have a table (or dataset) which you want to iterate trough with the smallest possible 
memory footprint on both application and database side. In general, your motivation is batch processing.
There are several methods to achieve similar, starting from using plain statement, Doctrine ORM method
stated in documentation
[https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/batch-processing.html](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/batch-processing.html),
special libraries for that purpose (like: [https://github.com/Ocramius/DoctrineBatchUtils](https://github.com/Ocramius/DoctrineBatchUtils)),
cursors on database levels, you name it.

This library allows you to use `iterate()` method which will modify your query, appending `LIMIT` and `OFFSET`
and iterate your recordset in pages, offloading pressure from booth database and application level. You may
configure a batch size (number of items per page), how rows should be yielded (whole row or just first column) as
well as you may pass a callable which you want to invoke after each bach of records, per example:

- you iterate through IDs of some entities
- you load each entity with ORM
- you modify each entity 
- you flush your changes and clear entity manager for each batch

(as in example given here: [https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/batch-processing.html](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/batch-processing.html))

However, note that quality of application of this method depends on many factors and data consistency may be
jeopardised. Some useful recommendations:

- always write queries without `LIMIT`/`OFFSET`.
- make sorting stable (per example, add at the end `ORDER BY id ASC` and, if possible, add order by some timestamped 
field, like "created_at") 
- iterate() is not executed within transaction, if data consistency can be impacted by that, wrap everything in
transaction, or lock affected tables, or use some other method to batch process data.


[<< Proposed solution](proposed-solution.md) | [Table of contents](index.md) | [Twig support >>](twig-support.md)
