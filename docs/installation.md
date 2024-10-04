Installation
============

Use composer to install the bundle:

```bash
composer require "runopencode/query-resources-loader-bundle"
```

Register bundle to your `bundles.php`:

```php
return [
    // ...
    RunOpenCode\Bundle\QueryResourcesLoader\QueryResourcesLoaderBundle::class => ['all' => true],
    // ...
]
```

## Configuration

Bundle will work out of the box with reasonable defaults, but you may want to configure it to suit your needs. Here is
the example of default configuration, which you can override in your `config/packages/query_resources_loader.yaml`:

```yaml
# config/packages/query_resources_loader.yaml
runopencode_query_resources_loader:
    default_executor: ~
    cache:
        pool: cache.app
        default_ttl: ~
    # Optionally, you may configure Twig loader, which will be explained later.
    twig:
        paths:
            '%kernel.project_dir%/src/Reporting/query': 'reporting'
        globals:
            year: 2024
            budget_limit: 1_000_000
```

- `default_executor` - Name of the executor which will be used as default executor of your queries. If not provided,
  first registered executor will be used as default. Since bundle relies on `doctrine/dbal` for SQL queries, this will
  be your default connection name (connection service name), which is `doctrine.dbal.default_connection` by default. If
  you want to use different executor as default, you can provide it here.
- `cache.pool` - Name of the cache pool which will be used to cache compiled queries. If not provided, `cache.app` will
  be used. This cache pool must be instance of `Symfony\Contracts\Cache\CacheInterface` and it is used by cache
  middleware to cache query results.
- `cache.default_ttl` - Default time to live for cached queries. If not provided, `null` will be used, which means that
  cache will never expire.

### Twig Loader configuration

Bundle provides Twig loader which can be used to load queries from Twig templates. It allows you to use Twig to build
complex queries instead of using query builder. This is very useful feature when you want to build queries based on user
input, or you want to build queries based on some complex logic. Using Twig to write your own complex queries is
described in more details in [Twig support](twig-support.md) documentation.

In regards to configuration, Twig environment for query loader is separate from Twig environment used for rendering your
templates. This means that if you want to configure Twig differently for query loader, you can do so, but it has to be
configured under `runopencode_query_resources_loader.twig` key in your configuration.

How to configure Twig is described
in [Twig documentation](https://symfony.com/doc/current/reference/configuration/twig.html).

Most common configuration is to provide paths to your query templates and to provide some global variables which can be
used in your queries.

When it comes to paths, you need to provide a path to directory where your query templates are stored, and you need to
provide a namespace for that path. Namespace is used to distinguish between different paths, so you can have multiple
paths. Following configuration example given above, referencing template on path
`%kernel.project_dir%/src/Reporting/query/common.ledger.sql.twig` would be done with
`@reporting/common.ledger.sql.twig`.

Read more about Twig support in [Twig support](twig-support.md) documentation.
