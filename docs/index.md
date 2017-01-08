Query resources loader
======================

In any reporting application, a long query statements (per example, an
SQL statements) are used to fetch/load data from database. There
are several places where those statements can residue, database view, 
stored procedure, or, as usual, within an application code.

This bundle allows you to store your queries in separate files within
`Resources\query` bundle and load and/or execute them via one simple 
service method call, keeping your code clean and well organised.  

# Table of content

- [Introduction](introduction.md)
- [Proposed solution](proposed-solution.md)
- [Using manager](using-manager.md)
- [Twig support](twig-support.md)
- [DoctrineDbalExecutorResult](doctrine-dbal-executor-result.md)

 







