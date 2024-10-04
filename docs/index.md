Query resources loader
======================

In any reporting application, a long query statements (per example, an SQL statements) are used to fetch/load data from
database. There are several places where those statements can residue, database view, stored procedure, or, as usual,
within an application code.

This bundle allows you to store your queries in separate files within`query` directory (or any other configured
directory, including `Bundle/Resources/query`) and load and/or execute them via one simple service method call, keeping
your code clean and well organised. **This is a main goal of this bundle - to allow you to separate your queries from
your application code.**

Beside reporting, this bundle can be combined with ORM (or ODM as well) to execute complex queries to fetch identifiers
of entities first, and then use those identifiers to fetch entities themselves. Reporting is just one of the obvious use
cases, you can use this bundle to execute any kind of query for whatever purpose.

## Table of content

- [Introduction](introduction.md)
- [Installation](installation.md)
- [Doctrine Dbal executor](doctrine-dbal-executor)
- [Query parameters](query-parameters.md)
- [Query options](query-options.md)
- [Twig support](twig-support.md)
- [Transaction support](transactions.md)
- [Caching](caching.md)
- [Middleware](middleware.md)
- [FAQ](faq.md)

