<?php

namespace SQLBuilder\Universal\Traits;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;

/**
 * Class OrderByTrait
 *
 * @package SQLBuilder\Universal\Traits
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
trait OrderByTrait
{
    /**
     * [
     *   [ 'column_name', 'ASC' ],
     *   [ 'column_name', new FuncCallExpr('rand', []) ],
     * ].
     */
    protected $orderByList = [];

    /**
     * > SELECT * FROM foo ORDER BY RAND(NOW()) LIMIT 1;
     * > SELECT * FROM foo ORDER BY 1,2,3;.
     *
     * > SELECT* FROM mytable ORDER BY
     *
     * @param      $byExpr
     * @param null $sorting
     *
     * @return $this
     */
    public function orderBy($byExpr, $sorting = null)
    {
        $this->orderByList[] = [$byExpr, $sorting];

        return $this;
    }

    /**
     * Clear orderByList array
     */
    public function removeOrderBy()
    {
        $this->orderByList = [];
    }

    /**
     * @param array $orderBy
     *
     * @return $this
     */
    public function setOrderBy(array $orderBy)
    {
        $this->orderByList = $orderBy;

        return $this;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function buildOrderByClause(BaseDriver $driver, ArgumentArray $args)
    {
        if (empty($this->orderByList)) {
            return '';
        }

        $sql = '';
        foreach ($this->orderByList as $orderBy) {
            if (is_string($orderBy[0])) {
                $sql .= ', ' . $orderBy[0];
                if (isset($orderBy[1]) && $orderBy[1]) {
                    $sql .= ' ' . $orderBy[1];
                }
            } elseif ($orderBy[0] instanceof ToSqlInterface) {
                $sql .= ', ' . $orderBy[0]->toSql($driver, $args);
            }
        }

        return ' ORDER BY' . ltrim($sql, ',');
    }
}
