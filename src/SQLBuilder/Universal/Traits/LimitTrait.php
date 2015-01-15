<?php
namespace SQLBuilder\Universal\Traits;
use SQLBuilder\Raw;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;

trait LimitTrait {

    protected $limit;


    /********************************************************
     * LIMIT clauses
     *******************************************************/
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function buildLimitClause(BaseDriver $driver, ArgumentArray $args)
    {
        if ($this->limit) {
            return ' LIMIT ' . intval($this->limit);
        }
        return '';
    }


}


