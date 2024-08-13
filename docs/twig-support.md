# Twig support

Sometimes, you will need to have complex queries which are built in runtime, depending on certain parameters provided by
user.

That is a major advantage of using some sort of query builder (like Doctrine's query builder) which allows you to do
implement pretty complex query building logic.

That is possible to do with string manipulation as well, however, code do tend to be quite complex and hard to interpret
and maintain.

Query building is a desirable feature, and Twig can help you in doing such thing.

## This is Twig, but new environment, new instance

Before you start using Twig in your queries, you should note several important things:

- Twig environment used in query resources loader is not the same environment used in rendering your templates. That
  means that extensions that you have registered for Twig are not available, simply because Symfony's Twig environment
  is used for rendering template, this environment is for building complex queries.
- You can, of course, create/register your own extensions for query loader Twig environment, register it as a service
  and tag it with `runopencode.query_resources_loader.twig.extension` tag.
- You cannot load Twig templates from `templates` directories (at least not with default settings), this mixing is not
  something that is desirable nor wanted
- Same principle applies if you want to override queries from other bundles as if you want to override bundle's
  templates (place query file with same name as in bundle's `Resources/query` directory in your `query` directory on
  path `bundles/BundleName/query/...`).

In general - bundle uses a new instance of Twig environment, it checks files in `query`, `Resources/query` and other
configured directories.

## Some examples

How you are going to use power of Twig is up to you, but here is general idea with simple example:

```php
$loader->execute('common.ledger.sql', DbalParameters::create()
    ->dateTimeImmutable('from', $filters->getFrom())
    ->dateTimeImmutable('to', $filters->getTo())
    ->integer('account', $filters->getAccount())
);
```

So, as you can see, year and budget limit can depend on, per example, user input. However, we can use same query with
little help of Twig:

```sql

SELECT *
FROM expenses_table ET

WHERE 1 # A trick, "WHERE 1" always evaluate to TRUE, so you can add other conditions with AND

    {% if
from is not null %}
    AND
    ET.from > :
from
    {% endif %}

    {% if to is not null %}
    AND
    ET.to > : to
    {% endif %}

    {% if account is not null %}
    AND
    ET.account = : account
    {% endif %}
```

**Note that you can pass extra parameters when executing statement**, Doctrine will not complain if you have passed
parameter that is not used in prepared statement, per example:

```php
$loader->execute('common.ledger.sql', DbalParameters::create()
    ->integer('year', $filters->getYear())   
    ->set('flag', $filters->getFlag())
);
```

```sql
SELECT *
FROM expenses_table ET

WHERE 1 {% if flag %}

    AND

    ET.year = :year

{% endif %}    
```

## Extension delivered out of the box

Extension `RunOpenCode\Bundle\QueryResourcesLoader\Twig\Extension\DoctrineOrmExtension`is delivered with this bundle, it
has several Twig functions/filters that can be of use if you use Doctrine ORM as well, per example:

- `table_name`: will provide you with table name for given full qualified class name of your entity.
- `join_table_name`: will provide you with table name for given full qualified class name of your entity and its
  property name which holds relation.
- `column_name`: will provide you with column name for given full qualified class name of your entity and its property
  name.
- `primary_key_column_name`: will provide you with primary key column name for given full qualified class.

These functions are useful if you expect changes in naming strategies of generated tables/column, or you expect changes
in general (early stage of development).
See [DoctrineOrmExtension.php](../src/RunOpenCode/Bundle/QueryResourcesLoader/Twig/Extension/DoctrineOrmExtension.php)
for more details and full list of functions/filters.

Usage example:

```sql
SELECT 

ET.{{ 'someProperty'|column_name('Full\\Qualified\\Entity\\Name') }} as alias_name

FROM {{ 'Full\\Qualified\\Entity\\Name'|table_name }} ET    
```

[<< Twig support](twig-support.md) | [Table of contents](index.md) | [DoctrineDbalExecutorResult >>](doctrine-dbal-executor-result.md)
