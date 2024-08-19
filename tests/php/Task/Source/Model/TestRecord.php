<?php

namespace Cambis\SilverstripePruner\Tests\Task\Source\Model;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

final class TestRecord extends DataObject implements TestOnly
{
    private static string $table_name = 'Pruner_TestRecord';
}
