<?php
namespace SQLBuilder\Traits;
use SQLBuilder\RawValue;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use SQLBuilder\Syntax\Conditions;
use LogicException;

trait WhereTrait {

    protected $where;

    public function where($expr = NULL , array $args = array()) {
        if (!$this->where) {
            $this->where = new Conditions;
        }
        if ($expr) {
            if (is_string($expr)) {
                $this->where->appendExpr($expr, $args);
            } else {
                throw new LogicException("Unsupported argument type of 'where' method.");
            }
        }
        return $this->where;
    }

    public function buildWhereClause(BaseDriver $driver, ArgumentArray $args) {
        if ($this->where && $this->where->hasExprs()) {
            return ' WHERE ' . $this->where->toSql($driver, $args);
        }
        return '';
    }



}

