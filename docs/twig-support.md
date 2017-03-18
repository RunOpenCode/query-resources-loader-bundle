# Twig support

Sometimes, you will need to have complex queries which are built in runtime,
depending on certain parameters provided by user.
 
That is a major advantage of using some sort of query builder (like 
Doctrine's query builder) which allows you to do implement pretty complex
query building logic. 

That is possible to do with string manipulation as well, however, code
do tend to be quite complex and hard to interpret and maintain.

However, query building is desirable feature, and Twig can help you
in doing such thing.

## This is Twig, but new environment, new instance

Before you start using Twig in your queries, you should note several important
things:

- Twig environment used in query resources loader is not the same environment
used in rendering your templates. That means that extensions that you have
registered for Twig are not available, simply because Symfony's Twig environment
is used for rendering template, this environment is for building complex 
queries.
- You can, of course, create/register your own extensions for query loader
Twig environment, register it as a service and tag it with 
`run_open_code.query_resources_loader.twig.extension` tag.
- You can not load Twig templates from `Resources/views` directories (at least
not with default settings), this mixing is not something that is desirable
nor wanted
- Same principle applies if you want to override queries from other bundle as
if you want to override bundle's templates

In general - bundle uses new instance of Twig environment, it checks 
files in `Resources/query` directory.

## Some examples

How you are going to use power of Twig is up to you, but here is general 
idea with simple example:


    $this->get('roc.query_loader')->execute('@MyAppReporting/common.ledger.sql', array(
        'year' => (!empty($year)) ? $year : null,
        'limit' => (!empty($limit)) ? $limit : null
    ));
    
So, as you can see, year and budget limit can depend of, per example, user
input. However, we can use same query with little help of Twig:    
    
    SELECT * FROM expenses_table ET
    
    WHERE
    
    1   # A trick, this always evaluate to true
    
    {% if year is not null %}
    
        AND
    
        ET.year = :year
    
    {% endif %}
    
    {% if limit is not null %}
    
    AND
    
    ET.budget <= :limit
    
    {% endif %}
        
    ;
            
            
**Note that you can pass extra parameters when executing statement**, Doctrine
will not complain if you have passed parameter that is not used in prepared 
statement, example:

    $this->get('roc.query_loader')->execute('@MyAppReporting/common.ledger.sql', array(
        'year' => $year,
        'flag' => $flag
    ));
    
    
and query:
    
    SELECT * FROM expenses_table ET
    
    WHERE   
    
    {% if flag == true %}
    
        AND
    
        ET.year = :year
    
    {% endif %}    
    
## Extension delivered out of the box
    
Extension `RunOpenCode\Bundle\QueryResourcesLoader\Twig\DoctrineOrmExtension`
is delivered with this bundle, it has two Twig functions/filters that can be of use:
    
- `table_name`: will provide you with table name for given full qualified class
name of your entity
- `column_name`: will provide you with column name for given full qualified class
name of your entity and its property name
 
These functions are useful if you expect changes in naming strategies of 
generated tables/column, or you expect changes in general (early stage
of development). 

Usage example:

    SELECT 

    ET.{{ 'someProperty'|column_name('Full\\Qualified\\Entity\\Name') }} as alias_name
 
    FROM {{ 'Full\\Qualified\\Entity\\Name'|table_name }} ET
        

[<< Twig support](twig-support.md) | [Table of contents](index.md) | [DoctrineDbalExecutorResult >>](doctrine-dbal-executor-result.md)
