# DoctrineDbalExecutorResult

Bundle, by default, provides you with support for executing SQL statements
against a relational database via Doctrine Dbal.

Result set is provided as instance of
`RunOpenCode\Bundle\QueryResourcesLoader\Executor\DoctrineDbalExecutorResult`,
which is wrapper of Doctrine's Dbal `Doctrine\DBAL\Driver\Statement` implementation
with additional, useful, utility methods that can improve your productivity
when working with SQL queries 
(see `RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutionResultInterface`):

- `getSingleScalarResult()` - Get single scalar result.
- `getSingleScalarResultOrDefault()` - Get single scalar result or  default
value if there are no results of executed SELECT statement.
- `getSingleScalarResultOrNull()` - Get single scalar result or NULL value
if there are no results of executed SELECT statement.
- `getScalarResult()` - Get collection of scalar values.
- `getScalarResultOrDefault()` - Get collection of scalar vales,
or default value if collection is empty.
- `getScalarResultOrNull()` - Get collection of scalar vales, or NULL value
if collection is empty.
- `getSingleResult()` - Get single (first) row result from result set.
- `getSingleResultOrDefault()` - Get single (first) row result from result set
 or default value if result set is empty.
- `getSingleResultOrNull()` - Get single (first) row result from result set
or NULL value if result set is empty.

Note that `ExecutionResultInterface` implements `\Traversable` and `\Countable`.

[<< Using manager](using-manager.md) | [Table of contents](index.md) | [Transaction support >>](transactions.md)
