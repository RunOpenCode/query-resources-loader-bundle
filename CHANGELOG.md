# [8.2.0](https://github.com/RunOpenCode/query-resources-loader-bundle/compare/8.1.1...8.2.0) (2025-04-21)


### Features

* **loader:** Added possibility to configure default loader, to use raw loader or to use chained loader ([e38197c](https://github.com/RunOpenCode/query-resources-loader-bundle/commit/e38197cf4d87d037c42f3365805bd84063891279))

## [8.1.1](https://github.com/RunOpenCode/query-resources-loader-bundle/compare/8.1.0...8.1.1) (2025-04-20)


### Bug Fixes

* **cache:** Fixed issue with cache middleware not caching ([a63bbfd](https://github.com/RunOpenCode/query-resources-loader-bundle/commit/a63bbfd5f9a9c1edfe3db156f4f90332c09e6fe3))

# [8.1.0](https://github.com/RunOpenCode/query-resources-loader-bundle/compare/8.0.2...8.1.0) (2025-04-20)


### Bug Fixes

* **ci:** Fixing path to reusable workflow ([2dc9881](https://github.com/RunOpenCode/query-resources-loader-bundle/commit/2dc9881c5a2d831761d438fd398c952f4cc90fec))
* **ci:** Fixing semantic release workflow for github ([fde700a](https://github.com/RunOpenCode/query-resources-loader-bundle/commit/fde700a18f6125b5aeadc444a6f7da29e02d0fd3))
* **ci:** Remove scrutinizer from pipeline ([5f6573f](https://github.com/RunOpenCode/query-resources-loader-bundle/commit/5f6573f27b836ed45c3133992aa56232e5a25474))
* **test:** Fixed issue with CI and psalm ([21be7a3](https://github.com/RunOpenCode/query-resources-loader-bundle/commit/21be7a385e212e34246bc9910ae3d857a665ad67))


### Features

* **core:** Improved cache identity, improved cache not to use Doctrine internals, added raw query loader ([4a8eb83](https://github.com/RunOpenCode/query-resources-loader-bundle/commit/4a8eb83724d600d49093a57792bac114f22b394e))

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
