<?php

namespace Cambis\SilverstripePruner\Concern;

use SilverStripe\ORM\DataObject;

trait UpdateTruncatedClasses
{
    /**
     * An extension hook used to dynamically update the truncated tables.
     *
     * @param array<class-string<DataObject>> $truncatedClasses
     */
    abstract protected function updateTruncatedClasses(array &$truncatedClasses): void;
}
