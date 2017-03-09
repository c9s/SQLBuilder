<?php

namespace SQLBuilder\MySQL\Traits;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\MySQL\Syntax\Partition;

trait PartitionTrait
{
    protected $partitions;

    public function partitions($partitions)
    {
        if (is_array($partitions)) {
            $this->partitions = new Partition($partitions);
        } else {
            $this->partitions = new Partition(func_get_args());
        }

        return $this;
    }

    public function buildPartitionClause(BaseDriver $driver, ArgumentArray $args)
    {
        if ($this->partitions) {
            return $this->partitions->toSql($driver, $args);
        }

        return '';
    }
}
