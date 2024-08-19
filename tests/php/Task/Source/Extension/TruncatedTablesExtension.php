<?php

namespace Cambis\SilverstripePruner\Task\Source\Extension;

use Cambis\SilverstripePruner\Concern\UpdateTruncatedTables;
use Cambis\SilverstripePruner\Task\PruneSelectedORMTablesTask;
use SilverStripe\Core\Extension;

/**
 * @extends Extension<PruneSelectedORMTablesTask>
 */
final class TruncatedTablesExtension extends Extension
{
    use UpdateTruncatedTables;

    /**
     * @param string[] $truncatedTables
     */
    protected function updateTruncatedTables(array &$truncatedTables): void
    {
        $truncatedTables[] = 'TestRecord_Foo';
    }
}
