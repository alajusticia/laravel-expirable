# Changelog

All notable changes to `laravel-expirable` will be documented in this file.

## 2.2 - 2023-07-18

- Add `mode` key to `expirable.php` configuration file to specify the type of delete used by the `expirable:purge` command, value defaulting to `hard`.

- Add `--mode=hard/soft` option to `expirable:purge` command, taking the `mode` key in `expirable.php` configuration file if not specified, otherwise defaulting to `hard` for backward compatibility.

## 2.1 - 2023-07-14

- Add ability to pass which models to delete in argument of the `expirable:purge` command.
- Add `since` option for the `expirable:purge` command to delete only the models that have expired for at least a given period of time.

## 2.0 - 2023-03-07

- Add support for Laravel 10.
- Expirable trait: Remove deprecated `$dates` property and add expirable attribute to `$casts` property.
- Tests: Update XML configuration schema for PHPUnit.
- Tests: Use class based factories.
