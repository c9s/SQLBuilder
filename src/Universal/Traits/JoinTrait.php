<?php

namespace SQLBuilder\Universal\Traits;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Universal\Syntax\Join;

/**
 * Class JoinTrait
 *
 * @package SQLBuilder\Universal\Traits
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
trait JoinTrait
{
    /**
     * @var Join[]
     */
    protected $joins = [];

    /**
     * @param string      $table
     * @param null|string $alias
     *
     * @return \SQLBuilder\Universal\Syntax\Join
     */
    public function innerJoin($table, $alias = null)
    {
        $join          = new Join($table, $alias, 'INNER');
        $this->joins[] = $join;

        return $join;
    }

    /**
     * @param string      $table
     * @param null|string $alias
     *
     * @return \SQLBuilder\Universal\Syntax\Join
     */
    public function rightJoin($table, $alias = null)
    {
        $join          = new Join($table, $alias, 'RIGHT');
        $this->joins[] = $join;

        return $join;
    }

    /**
     * @param string      $table
     * @param null|string $alias
     *
     * @return \SQLBuilder\Universal\Syntax\Join
     */
    public function leftJoin($table, $alias = null)
    {
        $join          = new Join($table, $alias, 'LEFT');
        $this->joins[] = $join;

        return $join;
    }

    /**
     * @param string      $table
     * @param null|string $alias
     * @param null        $joinType
     *
     * @return \SQLBuilder\Universal\Syntax\Join
     */
    public function join($table, $alias = null, $joinType = null)
    {
        $join          = new Join($table, $alias, $joinType);
        $this->joins[] = $join;

        return $join;
    }

    /**
     * @return \SQLBuilder\Universal\Syntax\Join[]
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function buildJoinClause(BaseDriver $driver, ArgumentArray $args)
    {
        if (empty($this->joins)) {
            return '';
        }
        $sql = '';
        foreach ($this->joins as $join) {
            $sql .= $join->toSql($driver, $args);
        }

        return $sql;
    }
}
