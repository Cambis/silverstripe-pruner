<?php

namespace Cambis\SilverstripePruner\Task\Source\Extension;

use Cambis\SilverstripePruner\Concern\UpdateTruncatedClasses;
use Cambis\SilverstripePruner\Task\PruneSelectedORMTablesTask;
use Cambis\SilverstripePruner\Tests\Task\Source\Model\TestRecord;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataObject;

/**
 * @extends Extension<PruneSelectedORMTablesTask>
 */
final class TruncatedClassesExtension extends Extension
{
    use UpdateTruncatedClasses;

    /**
     * @param array<class-string<DataObject>> $truncatedClasses
     */
    protected function updateTruncatedClasses(array &$truncatedClasses): void
    {
        $truncatedClasses[] = TestRecord::class;
    }
}
