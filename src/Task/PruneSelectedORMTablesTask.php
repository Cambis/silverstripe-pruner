<?php

namespace Cambis\SilverstripePruner\Task;

use Override;
use SilverStripe\Control\Director;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use Throwable;
use function array_key_exists;
use const PHP_EOL;

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

    /**
     * @var string[]
     */
    private static array $truncated_tables = [];

    private static bool $can_run_in_production = false;

    /**
     * @var string[]
     */
    private array $clearedTables = [];

    #[Override]
    public function run($request): void
    {
        // Obtain via injector so we can overload isLive() during testing
        $director = Injector::inst()->get(Director::class);

        if ($director::isLive() && !((bool) self::config()->get('can_run_in_production'))) {
            echo '🚨 This task cannot be run in a production environment! 🚨' . PHP_EOL;

            return;
        }

        if ($request->getVar('confirm') === null) {
            echo '⚠️ Are you sure? Please add ?confirm=1 to the URL to confirm. ⚠️' . PHP_EOL;

            return;
        }

        DB::get_conn()->withTransaction(function (): void {
            /** @var array<class-string<DataObject>> $truncatedClasses */
            $truncatedClasses = (array) self::config()->get('truncated_classes');

            /** @var string[] $truncatedTables */
            $truncatedTables = (array) self::config()->get('truncated_tables');

            foreach ($truncatedClasses as $className) {
                $tableName = DataObject::getSchema()->tableName($className);
                $this->truncateTable($tableName);
            }

            foreach ($truncatedTables as $tableName) {
                $this->truncateTable($tableName);
            }
        });
    }

    private function truncateTable(string $tableName): void
    {
        if (array_key_exists($tableName, $this->clearedTables)) {
            DB::alteration_message($tableName . ' already truncated, skipping.', 'deleted');

            return;
        }

        DB::alteration_message('Truncating table ' . $tableName . '.', 'deleted');

        try {
            DB::get_conn()->clearTable($tableName);
        } catch (Throwable) {
            DB::alteration_message("Couldn't truncate table " . $tableName .
                " as it doesn't exist.", 'deleted');
        }

        $this->clearedTables[] = $tableName;
    }
}
