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


[FAQ](faq.md) | [Table of contents](index.md)
