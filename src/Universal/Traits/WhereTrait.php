<?php

namespace SQLBuilder\Universal\Traits;

use InvalidArgumentException;
use SQLBuilder\ArgumentArray;
use SQLBuilder\ConditionsInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Universal\Syntax\Conditions;

/**
 * Class WhereTrait
 *
 * @package SQLBuilder\Universal\Traits
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
trait WhereTrait
{
    /**
     * @var Conditions
     */
    protected $where;

    /**
     * The arguments here are always binding to variables, won't be deflated to sql query.
     *
     * Example:
     *
     *     where('name = :name', [ 'name' => 'name' ]);
     *
     * @param string|array|null $expr
     * @param array             $args
     *
     * @return \SQLBuilder\Universal\Syntax\Conditions
     * @throws \InvalidArgumentException
     */
    public function where($expr = null, array $args = [])
    {
        if (!$this->where) {
            $this->where = new Conditions();
        }
        if ($expr) {
            if (is_string($expr)) {
                $this->where->raw($expr, $args);
            } elseif (is_array($expr)) {
                foreach ((array)$expr as $key => $val) {
                    $this->where->equal($key, $val);
                }
            } else {
                throw new InvalidArgumentException("Unsupported argument type of 'where' method.");
            }
        }

        return $this->where;
    }

    /**
     * @param ConditionsInterface $where
     */
    public function setWhere(ConditionsInterface $where)
    {
        $this->where = $where;
    }

    /**
     * @return \SQLBuilder\Universal\Syntax\Conditions
     */
    public function getWhere()
    {
        if ($this->where) {
            return $this->where;
        }

        return $this->where = new Conditions();
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function buildWhereClause(BaseDriver $driver, ArgumentArray $args)
    {
        if ($this->where && count($this->where->exprs)) {
            return ' WHERE ' . $this->where->toSql($driver, $args);
        }

        return '';
    }
}
