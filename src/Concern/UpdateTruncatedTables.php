<?php

namespace Cambis\SilverstripePruner\Concern;

trait UpdateTruncatedTables
{
    /**
     * An extension hook used to dynamically update the truncated tables.
     *
     * @param string[] $truncatedTables
     */
    abstract protected function updateTruncatedTables(array &$truncatedTables): void;
}
