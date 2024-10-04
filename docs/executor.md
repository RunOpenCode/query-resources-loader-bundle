Executor
========

Executor is underlying implementation of `RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryExecutorInterface` which
actually executes your query and returns instance of
`RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface`.

You will never work directly with executor, you may only choose which executor to use for your query
via [options configuration](query-options.md). You may also configure default executor for your queries
via [bundle configuration](installation.md) if you omit options configuration when executing query.

This bundle provides you with Doctrine Dbal executors which will be configured for each Doctrine Dbal connection you
have in your application. However, you may provide your own executor implementation and configure it for your queries.
In order to do so, you will need to implement `RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryExecutorInterface`. 