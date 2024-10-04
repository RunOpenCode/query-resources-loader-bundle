# Middleware

Library internally uses a middleware pattern to load and execute queries. Middlewares provide you with possibility to
hook into query execution process and modify or extend it. There are some very neat use cases for middlewares to be used
when loading and executing queries:

- **Caching**: You can cache results of queries to speed up your application. This library provides a middleware which
  caches results of queries for you. How you can use it is described in details here.
- **Logging**: You can log queries and their execution time, as well as each phase of query execution process (loading,
  modifying, executing, etc.). You may also trace cache hit and cache misses for each cached query.
- **Profiling**: You may develop your own profiling pane for Symfony web debug toolbar, or any other profiling tool, to
  show how much time each query takes to execute, how much time it takes to load, etc. This feature is on roadmap for
  this library.
- **Retry**: You may retry query execution in case of failure.
- **Load balancing**: You may execute queries on different database connections, or even on different database servers.
- **Security**: You may check if user has permission to execute query, or if query is safe to execute.
- **Query modification**: You may modify query before execution, for example, to add some additional conditions, or to
  change query completely.
- **Failover**: You may execute query on different database connection in case of failure.

These are just some of the ideas how you can use middlewares to extend and modify query execution process.

## Implementing your own middleware

In order to do so, you need to implement `RunOpenCode\Bundle\QueryResourcesLoader\Contract\MiddlewareInterface`
interface and register it as a service in your Symfony application tagged with
`runopencode.query_resources_loader.middleware`. With priority attribute you may define order of middleware execution.
Do note that this library already provides some middlewares out of the box with their own priorities:

- `RunOpenCode\Bundle\QueryResourcesLoader\Cache\CacheMiddleware` with priority 1000. Ideally, you should register your
  caching middleware with priority lower than 1000 so caching can be applied first.
- `RunOpenCode\Bundle\QueryResourcesLoader\Loader\LoaderMiddleware` with priority 500. This middleware is responsible
  for loading query from its location.

### Example

In example below, we will implement a middleware which logs each query execution time to Symfony's logger.

```php
<?php
 
declare(strict_types=1);

namespace App\Middleware;

use Psr\Log\LoggerInterface;
use RunOpenCode\Bundle\QueryResourcesLoader\Contract\MiddlewareInterface;

final readonly class LoggerMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface $logger)
    {
        // noop
    }

    public function __invoke(string $query, Parameters $parameters, Options $options, callable $next): iterable
    {
        $start  = \microtime(true);
        $result = $next($query, $parameters, $options);
        $end    = \microtime(true);

        $this->logger->info(sprintf('Query "%s" executed in %f seconds.', $query, $end - $start));

        return $result;
    }
}
```

