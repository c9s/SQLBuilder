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
use SQLBuilder\ParamMarker;
use SQLBuilder\Universal\Syntax\Join;
use SQLBuilder\Universal\Syntax\LeftJoin;
use SQLBuilder\Universal\Syntax\RightJoin;
use SQLBuilder\Universal\Syntax\IndexHint;

trait JoinTrait {
    protected $joins = array();

    public function rightJoin($table, $alias = NULL) {
        $join = new RightJoin($table, $alias);
        $this->joins[] = $join;
        return $join;
    }

    public function leftJoin($table, $alias = NULL) {
        $join = new LeftJoin($table, $alias);
        $this->joins[] = $join;
        return $join;
    }

    public function join($table, $alias = NULL, $joinType = NULL) {
        $join = new Join($table, $alias, $joinType);
        $this->joins[] = $join;
        return $join;
    }

    public function getJoins() {
        return $this->joins;
    }

    public function buildJoinClause(BaseDriver $driver, ArgumentArray $args) {
        $sql = '';
        if (!empty($this->joins)) {
            foreach($this->joins as $join) {
                $sql .= $join->toSql($driver, $args);
            }
        }
        return $sql;
    }
}

