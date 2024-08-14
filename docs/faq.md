# FAQ

## Query in Twig file says that it cannot find function/test/filter which I have registered in my Twig extension for view layer.

This bundle uses completely new instance of Twig environment, completely separated from the one used for rendering of
views. Functions/tests/filters are not shared, mostly because it does not make sense to share them as well as for
security purposes.

On top of that, both instances have their own registry of files, so it is not even possible to import templates from
each other.

## Why `dmaicher/doctrine-test-bundle` does not work with queries having transaction statements in it?

First, it is not recommended to have `START TRANSACTION`, `COMMIT` statements in your queries at all, if you need to
have them, you can use `transactional()` method of `ExecutorInterface`.

That being said, `dmaicher/doctrine-test-bundle`tracks transactions of `Connection` object. If you use
`START TRANSACTION` statement in your query, `dmaicher/doctrine-test-bundle` is not able to roll back them, because
those are not explicitly tracked by `Connection` object.

## Legacy support

Prior to version 8, main interface of the library was
`RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface`. This interface is replaced with
`RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface` which should be used from now on.

However, to support gradual migration, interface `ManagerInterface` is still available and can be used, but without
`iterate()` method which is not supported in legacy interface. API for iterating over records/tables will not be
introduced in future versions as part of this library.

Support for `ManagerInterface` will be removed in version 9.

[FAQ](faq.md) | [Table of contents](index.md)
