<?php
namespace SQLBuilder\Traits;
use SQLBuilder\Raw;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use SQLBuilder\Syntax\Join;
use SQLBuilder\Syntax\IndexHint;


trait JoinTrait {

    protected $joins = array();

    protected $indexHintOn = array();

    public function join($table, $alias = NULL) {
        $join = new Join($table, $alias);
        $this->joins[] = $join;
        return $join;
    }

    public function getJoins() {
        return $this->joins;
    }

    public function getLastJoin() {
        return end($this->joins);
    }

    public function indexHintOn($tableRef) {
        $hint = new IndexHint;
        $this->indexHintOn[$tableRef] = $hint;
        return $hint;
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

    public function buildJoinIndexHintClause(BaseDriver $driver, ArgumentArray $args)
    {
        if (empty($this->indexHintOn)) {
            return '';
        }
        $clauses = array();
        foreach($this->indexHintOn as $hint) {
            $clauses[] = $hint->toSql($driver, $args);
        }
        return ' ' . join(' ', $clauses);
    }

}

