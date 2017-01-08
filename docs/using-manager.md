# Using manager

Manager defines 3 public methods:

    interface ManagerInterface
    {
        /**
         * Get Query source by its name.
         *
         * @param string $name Name of Query source code.
         * @param array $args Arguments for modification/compilation of Query source code.
         * @return string SQL statement.
         */
        public function get($name, array $args = array());
    
        /**
         * Execute Query source.
         *
         * @param string $name Name of Query source code.
         * @param array $args Arguments for modification/compilation of Query source code, as well as params for query statement.
         * @param array $types Types of parameters for prepared statement.
         * @param null|string $executor Executor name.
         * @return mixed Execution results.
         */
        public function execute($name, array $args = array(), array $types = array(), $executor = 'default');
    
        /**
         * Check if manager have the Query source code by its given name.
         *
         * @param string $name The name of the Query source to check if can be loaded.
         * @return bool TRUE If the Query source code is handled by this manager or not.
         */
        public function has($name);
    }
    
    
While first parameter `$name` is bundle resource locator syntax of query 
which you are trying to load/execute, second parameter allows you to use
parametrised query statements (prepared statements), as well as building
complex queries depending of passed arguments (see [Twig support](twig-support.md)).

Third, optional, parameter `$types` allows you to explicitly specify type
of parameters for prepared statements, which is useful when using, per example,
`WHERE IN` in your SQL statements.

## Query executor

Method `get()` will just provide you with loaded and parsed query string,
while method `execute()` will execute the query and provide you with the
query result. Return type of `execute()` method depends on used executor.

Note that used Manager implementation is not database agnostic, nor it 
is intended to be. Library provides you, for now, with 
`RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutor` 
which can be used for executing queries against relational databases
supported by Doctrine Dbal.

When executing SQL statements with `DoctrineDbalExecutor`, result set is
returned as instance of
`RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutorResult`
class, which has several utility functions that can increase your productivity
when working with your SQL result sets.

You can, of course, implement your own query executor, by implementing
`RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface` interface
and registering it in service container with tag name 
`run_open_code.query_resources_loader.executor` with attribute `name` 
which you can use when executing query in multi-executor environment.
 
Default executor is the first registered executor, or, it can be
configured under `run_open_code_query_resources_loader.default_executor`
key where you should state the service name of your executor, example:

    run_open_code_query_resources_loader:
        default_executor: my_executor_service_name

## How to use manager

It is strongly advised to inject manager into your repository class/service
as in example in previous chapter, however, you can get service from service
container as well:

    $this->get('roc.query_loader')->execute('@MyAppReporting/common.ledger.sql', array(
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
    
        
And by using Twig within this bundle, you can do a realy serious and complex
query building as well.        

[Proposed solution](proposed-solution.md) | [Table of contents](index.md) | [Twig support](twig-support.md)   
 
