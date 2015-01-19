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
use SQLBuilder\Universal\Syntax\Conditions;
use InvalidArgumentException;

trait WhereTrait {

    protected $where;

    /**
     * The arguments here are always binding to varibles, won't be deflated to sql query
     *
     * Example:
     *
     *     where('name = :name', [ 'name' => 'name' ]);
     */
    public function where($expr = NULL , array $args = array()) {
        if (!$this->where) {
            $this->where = new Conditions;
        }
        if ($expr) {
            if (is_string($expr)) {
                $this->where->appendExpr($expr, $args);
            } else {
                throw new InvalidArgumentException("Unsupported argument type of 'where' method.");
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

