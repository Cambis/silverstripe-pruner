# Silverstripe Pruner

A simple developer utility to clear database tables. This module provides a configurable task that truncates database tables inside of a transaction.

## Prerequisites 🦺

```sh
php ^7.4 || ^8.0
silverstripe/framework ^4.0 || ^5.0
```

## Installation 👷‍♀️

Install via composer.

```sh
composer require --dev cambis/silverstripe-pruner
```

## Configuration 🚧

Create a configuration file.

```yml
---
Name: app_pruner
---
Cambis\SilverstripePruner\Task\PruneSelectedORMTablesTask:
  # List the fqn names of the DataObjects you want to truncate
  truncated_classes:
    - My\Record\To\Truncate
  # Any extra tables such as those from silverstripe/versioned etc.
  truncated_tables:
    - Truncate_Live
    - Truncate_Versions
  # Defaults to false, add this line if you want to run the task in a production environment
  can_run_in_production: true
```

## Usage 🏃🏃🏃

```sh
vendor/bin/sake dev/tasks/prune-selected-orm-tables confirm=1
```
