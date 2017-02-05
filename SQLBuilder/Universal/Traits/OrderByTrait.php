<?php

namespace SQLBuilder\Universal\Traits;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;

trait OrderByTrait
{
    /**
     * [
     *   [ 'column_name', 'ASC' ],
     *   [ 'column_name', new FuncCallExpr('rand', []) ],
     * ].
     */
    protected $orderByList = array();

    /**
     > SELECT * FROM foo ORDER BY RAND(NOW()) LIMIT 1;
     > SELECT * FROM foo ORDER BY 1,2,3;.
     
     > SELECT* FROM mytable ORDER BY
     */
    public function orderBy($byExpr, $sorting = null)
    {
        $this->orderByList[] = array($byExpr, $sorting);

        return $this;
    }

    public function removeOrderBy()
    {
        $this->orderByList = array();
    }

    public function setOrderBy(array $orderBy)
    {
        $this->orderByList = $orderBy;

        return $this;
    }

    public function buildOrderByClause(BaseDriver $driver, ArgumentArray $args)
    {
        if (empty($this->orderByList)) {
            return '';
        }

        $sql = '';
        foreach ($this->orderByList as $orderBy) {
            if (is_string($orderBy[0])) {
                $sql .= ', '.$orderBy[0];
                if (isset($orderBy[1]) && $orderBy[1]) {
                    $sql .= ' '.$orderBy[1];
                }
            } elseif ($orderBy[0] instanceof ToSqlInterface) {
                $sql .= ', '.$orderBy[0]->toSql($driver, $args);
            }
        }

        return ' ORDER BY'.ltrim($sql, ',');
    }
}
