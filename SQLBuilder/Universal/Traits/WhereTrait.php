<?php

namespace SQLBuilder\Universal\Traits;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Syntax\Conditions;
use InvalidArgumentException;

trait WhereTrait
{
    protected $where;

    /**
     * The arguments here are always binding to varibles, won't be deflated to sql query.
     *
     * Example:
     *
     *     where('name = :name', [ 'name' => 'name' ]);
     */
    public function where($expr = null, array $args = array())
    {
        if (!$this->where) {
            $this->where = new Conditions();
        }
        if ($expr) {
            if (is_string($expr)) {
                $this->where->appendExpr($expr, $args);
            } else if (is_array($expr)) {
                foreach ($expr as $key => $val) {
                    $this->where->equal($key, $val);
                }
            } else {
                throw new InvalidArgumentException("Unsupported argument type of 'where' method.");
            }
        }

        return $this->where;
    }

    public function setWhere(Conditions $where)
    {
        $this->where = $where;
    }

    public function getWhere()
    {
        if ($this->where) {
            return $this->where;
        }

        return $this->where = new Conditions();
    }

    public function buildWhereClause(BaseDriver $driver, ArgumentArray $args)
    {
        if ($this->where && $this->where->hasExprs()) {
            return ' WHERE '.$this->where->toSql($driver, $args);
        }

        return '';
    }
}
