# Changelog

All notable changes to Laravel Expirable are documented in this file.

The project follows [Semantic Versioning](https://semver.org/).

## [2.6.0](https://github.com/alajusticia/laravel-expirable/releases/tag/v2.6.0) - 2026-10-11

- Improve PHPStan support for expirable models and queries.
  - Add a PHPStan extension, registered automatically through `extension.neon`, that preserves model and custom builder types for the `withExpired()`, `onlyExpired()`, `onlyEternal()` and `expiredSince()` scopes.
  - Fix the types of date parameters and return values, fluent methods (`$this`), `expireByKey()` IDs, and the `expirable()` / `dropExpirable()` schema macros.
  - Add regression tests for builders and relationships.

## [2.5.1](https://github.com/alajusticia/laravel-expirable/releases/tag/v2.5.1) - 2026-04-19

- Update vulnerable `phpunit/phpunit` dependency.

## [2.5.0](https://github.com/alajusticia/laravel-expirable/releases/tag/v2.5.0) - 2026-03-09

- Add Laravel 13 compatibility ([#6](https://github.com/alajusticia/laravel-expirable/pull/6)).

## [2.4.1](https://github.com/alajusticia/laravel-expirable/releases/tag/v2.4.1) - 2025-03-26

- Update README file.

## [2.4](https://github.com/alajusticia/laravel-expirable/releases/tag/v2.4) - 2025-03-01

- Add support for Laravel 12.

## [2.3](https://github.com/alajusticia/laravel-expirable/releases/tag/v2.3) - 2024-02-28

- Add support for Laravel 11.

## [2.2](https://github.com/alajusticia/laravel-expirable/releases/tag/v2.2) - 2023-07-18

Adds support for soft deletion in the `expirable:purge` command, by [@zmorris](https://github.com/zmorris) ([#5](https://github.com/alajusticia/laravel-expirable/pull/5)).

- Add `mode` key to the `expirable.php` configuration file to specify the type of delete used by the `expirable:purge` command, defaulting to `hard`.
- Add `--mode=hard/soft` option to the `expirable:purge` command. It falls back to the `mode` key of the `expirable.php` configuration file, then to `hard` for backward compatibility.

## [2.1](https://github.com/alajusticia/laravel-expirable/releases/tag/v2.1) - 2023-07-14

- Add ability to pass which models to delete as an argument of the `expirable:purge` command.
- Add `since` option to the `expirable:purge` command to delete only the models that have expired for at least a given period of time.

## [2.0](https://github.com/alajusticia/laravel-expirable/releases/tag/v2.0) - 2023-03-07

- Add support for Laravel 10.
- Expirable trait: remove deprecated `$dates` property and add the expirable attribute to the `$casts` property.
- Tests: update XML configuration schema for PHPUnit.
- Tests: use class-based factories.

## [1.8](https://github.com/alajusticia/laravel-expirable/releases/tag/v1.8) - 2023-07-18

Backport of 2.2 to the 1.x branch: adds support for soft deletion in the `expirable:purge` command, by [@zmorris](https://github.com/zmorris) ([#5](https://github.com/alajusticia/laravel-expirable/pull/5)).

- Add `mode` key to the `expirable.php` configuration file to specify the type of delete used by the `expirable:purge` command, defaulting to `hard`.
- Add `--mode=hard/soft` option to the `expirable:purge` command. It falls back to the `mode` key of the `expirable.php` configuration file, then to `hard` for backward compatibility.

## [1.7](https://github.com/alajusticia/laravel-expirable/releases/tag/v1.7) - 2023-07-14

Backport of 2.1 to the 1.x branch.

- Add ability to pass which models to delete as an argument of the `expirable:purge` command.
- Add `since` option to the `expirable:purge` command to delete only the models that have expired for at least a given period of time.

## [1.6.1](https://github.com/alajusticia/laravel-expirable/releases/tag/v1.6.1) - 2023-03-07

- Update README file.

## [1.6](https://github.com/alajusticia/laravel-expirable/releases/tag/v1.6) - 2022-02-09

- Add support for Laravel 9.
- Fix vulnerable dependencies (require `illuminate/database` >= 8.40.0).

## [1.5.2](https://github.com/alajusticia/laravel-expirable/releases/tag/v1.5.2) - 2021-10-06

- Use the `class_uses_recursive` Laravel helper to detect the `Expirable` trait in the purge command.

## [1.5.1](https://github.com/alajusticia/laravel-expirable/releases/tag/v1.5.1) - 2021-10-06

- Fix trait detection in the purge command.

## [1.5](https://github.com/alajusticia/laravel-expirable/releases/tag/v1.5) - 2021-09-16

Big changes, with no breaking changes, to avoid conflicts and compatibility problems with other packages:

- Use query builder macros instead of a custom Eloquent query builder.
- Use the trait-specific `bootExpirable()` method instead of overriding the parent's `boot()` method.

### Added

- The expiration date attribute is now automatically added to the list of attributes that should be mutated to dates.
- `extendLifetimeBy()` method.
- `shortenLifetimeBy()` method.
- `resetExpiration()` method.
- `getExpirationDate()` method.
- Migrations: reverse blueprint macro `dropExpirable()`.
- Tests for the new methods.

### Fixed

- Add missing dependencies in the `composer.json` file.

## [1.4](https://github.com/alajusticia/laravel-expirable/releases/tag/v1.4) - 2021-03-04

- Add support for PHP 8.

## [1.3.1](https://github.com/alajusticia/laravel-expirable/releases/tag/v1.3.1) - 2020-04-03

- Update dependencies for tests.

## [1.3](https://github.com/alajusticia/laravel-expirable/releases/tag/v1.3) - 2020-04-03

- Add support for Laravel 7.
- Update README file.

## [1.2](https://github.com/alajusticia/laravel-expirable/releases/tag/v1.2) - 2020-02-16

- Add tests.
- Update documentation.

## [1.1.1](https://github.com/alajusticia/laravel-expirable/releases/tag/v1.1.1) - 2020-02-04

- Register the `expirable:purge` command in the service provider.

## [1.1](https://github.com/alajusticia/laravel-expirable/releases/tag/v1.1) - 2020-02-02

- Add `php artisan expirable:purge` command to delete expired records from the database.
- Update README file.

## [1.0.1](https://github.com/alajusticia/laravel-expirable/releases/tag/v1.0.1) - 2020-02-02

- Update namespace.

## [1.0](https://github.com/alajusticia/laravel-expirable/releases/tag/v1.0) - 2019-10-19

- Initial release.
