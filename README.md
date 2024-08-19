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

You can also dynamically update the truncated tables using an extension hook. This library provides traits to with the hook method definitions.

```php
<?php

namespace App\Extension;

use Cambis\SilverstripePruner\Concern\UpdateTruncatedClasses;
use Cambis\SilverstripePruner\Concern\UpdateTruncatedTables;
use Cambis\SilverstripePruner\Task\PruneSelectedORMTablesTask;
use SilverStripe\Core\Extension;

/**
 * @extends Extension<PruneSelectedORMTablesTask>
 */
final class MyPrunerExtension extends Extension
{
    use UpdateTruncatedClasses;
    use UpdateTruncatedTables;

    protected function updateTruncatedClasses(array &$truncatedClasses): void
    {
        $truncatedClasses[] = \My\Record\To\Truncate::class;
    }

    protected function updateTruncatedTables(array &$truncatedTables): void
    {
        $truncatedTables[] = 'Truncate_Live';
        $truncatedTables[] = 'Truncate_Versions';
    }
}
```

## Usage 🏃🏃🏃

```sh
vendor/bin/sake dev/tasks/prune-selected-orm-tables confirm=1
```
