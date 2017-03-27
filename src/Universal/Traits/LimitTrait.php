<?php

namespace SQLBuilder\Universal\Traits;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;

/**
 * Class LimitTrait
 *
 * @package SQLBuilder\Universal\Traits
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
trait LimitTrait
{
    /**
     * @var int
     */
    protected $limit;

    /**
     * LIMIT clauses
     *
     *
     * @param $limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function buildLimitClause(BaseDriver $driver, ArgumentArray $args)
    {
        if ($this->limit) {
            return ' LIMIT ' . (int)$this->limit;
        }

        return '';
    }
}
