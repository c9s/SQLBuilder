<?php
namespace SQLBuilder\Traits;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\Raw;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use SQLBuilder\Syntax\Conditions;
use SQLBuilder\Syntax\Partition;
use LogicException;

trait PartitionTrait {

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
