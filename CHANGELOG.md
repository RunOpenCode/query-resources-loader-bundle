# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)and this project adheres to
[Semantic Versioning](http://semver.org/).

## Changelog

## [8.0.2] - 2024-08-15

### Fixed

- Fixed setting immutable date params with mutable date in
  `RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalParameters`.

## [8.0.1] - 2024-08-15

### Fixed

- Fixed nullability of array parameters of `RunOpenCode\Bundle\QueryResourcesLoader\Executor\Dbal\DbalParameters`.

## [8.0.0] - 2024-08-14

### Added

- New API for interacting with query resources loader,
  `RunOpenCode\Bundle\QueryResourcesLoader\Contract\QueryResourcesLoaderInterface`.
- Support for middleware in query resources loader.
- Support for caching of query results.

### Deprecated

- `RunOpenCode\Bundle\QueryResourcesLoader\Contract\ManagerInterface` is deprecated and will be removed in version 9.
- `RunOpenCode\Bundle\QueryResourcesLoader\Contract\ExecutorInterface` is deprecated and will be removed in version 9.
- No support for `iterate()` method in `ManagerInterface`.

### Removed

- Support for iterating over records/tables in library.