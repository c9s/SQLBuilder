<?php
namespace SQLBuilder\Syntax;
use SQLBuilder\Expression\ConditionsExpr;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use LogicException;

class Join implements ToSqlInterface
{
    public $condition;

    public $alias;

    protected $joinType;

    public function __construct($table, $alias = NULL)
    {
        $this->table = $table;
        $this->alias = $alias;
        $this->condition = new ConditionsExpr;
    }

    public function left() {
        $this->joinType = ' LEFT ';
        return $this;
    }

    public function right() {
        $this->joinType = 'RIGHT';
        return $this;
    }

    public function inner() {
        $this->joinType = 'INNER';
        return $this;
    }

    public function straight() {
        $this->joinType = 'STRAIGHT_JOIN';
        return $this;
    }

    public function on($conditionExpr = NULL, array $args = NULL)
    {
        if (is_string($conditionExpr)){
            $this->condition->appendRawExpr($conditionExpr, $args);
        }
        return $this->condition;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $sql = ' ';

        if ($this->joinType) {
            $sql .= $this->joinType;
        }

        $sql .= 'JOIN ' . $this->table;
        if ($this->alias) {
            $sql .= ' AS ' . $this->alias;
        }
        if ($this->condition->hasExprs()) {
            $sql .= ' ON (' . $this->condition->toSql($driver, $args) . ')';
        }
        return $sql;
    }

    public function _as($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    public function __call($m, $a) {
        if ($m == "as") {
            return $this->_as($a[0]);
        }
        throw new LogicException("Invalid method call: $m");
    }
}



