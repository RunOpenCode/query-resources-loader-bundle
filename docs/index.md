Query resources loader
======================

In any reporting application, a long query statements (per example, an SQL statements) are used to fetch/load data from
database. There are several places where those statements can residue, database view, stored procedure, or, as usual,
within an application code.

This bundle allows you to store your queries in separate files within`query` directory (or any other configured
directory, including `Bundle/Resources/query`) and load and/or execute them via one simple service method call, keeping
your code clean and well organised.

Beside reporting, this bundle can be combined with ORM (or ODM as well) to execute complex queries to fetch identifiers
of entities first, and then use those identifiers to fetch entities themselves.

# Table of content

- [Introduction](introduction.md)
- [Proposed solution](proposed-solution.md)
- [Using manager](using-manager.md)
- [Twig support](twig-support.md)
- [DoctrineDbalExecutorResult](doctrine-dbal-executor-result.md)
- [Transaction support](transactions.md)
- [FAQ](faq.md)
