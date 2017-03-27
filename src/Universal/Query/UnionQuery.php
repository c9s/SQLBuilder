<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;

/**
 * Class UnionQuery
 *
 * @package SQLBuilder\Universal\Query
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class UnionQuery implements ToSqlInterface
{
    public $queries = [];

    /**
     * @param \SQLBuilder\Universal\Query\SelectQuery $query
     *
     * @return $this
     */
    public function union(SelectQuery $query)
    {
        $args          = func_get_args();
        $this->queries = array_merge_recursive($this->queries, $args);

        return $this;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $subQueries = [];
        foreach ($this->queries as $q) {
            $subQueries[] = '(' . $q->toSql($driver, $args) . ')';
        }

        return implode(' UNION ', $subQueries);
    }
}
