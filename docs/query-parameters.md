# Query parameters

Your queries will certainly need parameters. To do so, you may use instance of class
`RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters` to set them. This class is not final, so you may extend it
and add your own methods to simplify setting parameters.

You will most likely use `set` method to set parameters. This method accepts three arguments:

- `name` - name of the parameter, string, required.
- `value` - value of the parameter, mixed, required.
- `type` - type of the parameter, string or int or enum, optional. Type will be dependent on the database abstraction
  you are using. For now, this library supports only `doctrine/dbal`, but that does not mean that you can not provide
  support for other database abstractions.

Parameters may also contain values which are not to be used for execution of the query, but for parsing and evaluation
of expressions within your query code, assuming you are using Twig (or any other language) for writing complex queries.

Providing parameter type is optional, but it is recommended to provide it. If parameter will be used only for evaluation
of Twig expression in your query, skip providing type as there is no value in providing it.

Example of setting parameters for query is given below:

```php
use RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters;

$params = Parameters::create()
    ->set('name', 'John', 'string')
    ->set('age', 30, 'integer');   
```

## DbalParameters

Assuming that you are using `doctrine/dbal` provided within this library, instead of using
`RunOpenCode\Bundle\QueryResourcesLoader\Model\Parameters` it is recommended to use
`RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalParameters` which is a subclass of `Parameters` and provides
additional methods for setting parameters which are specific to `doctrine/dbal` and based on `doctrine/dbal` types.

This class can be extended as well, and you may add your own methods to simplify setting parameters assuming you have
need for your own custom `doctrine/dbal` types.

Example of setting parameters for query is given below:

```php
use RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalParameters;

$params = DbalParameters::create()
    ->string('name', 'John')
    ->integer('age', 30)
    ->stringArray('roles', ['ROLE_USER', 'ROLE_ADMIN'])
    ->set('with_acl', true);
```

_NOTE_: `DbalParameters` is a subclass of `Parameters`, so you may use all methods provided by `Parameters` class. It is
recommended to use `set()` method for parameters used only for evaluation of Twig expressions.

You are encouraged to investigate both classes and get familiar with their methods and their purpose, besides the common
use case of setting parameters provided in this document.
