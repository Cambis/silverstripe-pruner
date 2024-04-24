<?php

namespace Cambis\SilverstripePruner\Task;

use SilverStripe\Control\Director;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use Throwable;

/**
 * Prune selected `\SilverStripe\ORM\DataObject` records from the database.
 *
 * @see \Cambis\SilverstripePruner\Tests\Task\PruneSelectedORMTablesTaskTest
 */
final class PruneSelectedORMTablesTask extends BuildTask
{
    /**
     * @var string
     */
    protected $title = 'Prune selected ORM tables from the database';

    private static string $segment = 'prune-selected-orm-tables';

    /**
     * @var array<class-string<DataObject>>
     */
    private static array $truncated_classes = [];

    private static bool $can_run_in_production = false;

    /**
     * @var array<class-string<DataObject>, bool>
     */
    private array $clearedTables = [];

    public function run($request): void
    {
        // Obtain via injector so we can overload isLive() during testing
        $director = Injector::inst()->get(Director::class);

        if ($director::isLive() && !((bool) self::config()->get('can_run_in_production'))) {
            echo 'This task cannot be run in a production environment!' . PHP_EOL;

            return;
        }

        if (is_null($request->getVar('confirm'))) {
            echo 'Are you sure? Please add ?confirm=1 to the URL to confirm.' . PHP_EOL;
        }

        DB::get_conn()->withTransaction(function (): void {
            /** @var array<class-string<DataObject>> $truncatedClasses */
            $truncatedClasses = (array) self::config()->get('truncated_classes');

            foreach ($truncatedClasses as $className) {
                if (array_key_exists($className, $this->clearedTables)) {
                    continue;
                }

                $tableName = DataObject::getSchema()->tableName($className);

                DB::alteration_message('Truncating table ' . $tableName, 'deleted');

                try {
                    DB::get_conn()->clearTable($tableName);
                } catch (Throwable) {
                    DB::alteration_message('Couldn\'t truncate table ' . $tableName .
                        ' as it doesn\'t exist', 'deleted');
                }

                $this->clearedTables[$className] = true;
            }
        });
    }
}
