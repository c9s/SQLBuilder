<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Universal\Traits\KeyTrait;

/**
 * Class Constraint
 *
 * @package SQLBuilder\Universal\Syntax
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class Constraint implements ToSqlInterface
{
    use KeyTrait;

    /**
     * @var null|string
     */
    protected $symbol;

    /**
     * Constraint constructor.
     *
     * @param null $symbol
     */
    public function __construct($symbol = null)
    {
        $this->symbol = $symbol;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = '';
        // constrain symbol is optional but only supported by MySQL
        if ($this->symbol) {
            $sql .= 'CONSTRAINT ' . $driver->quoteIdentifier($this->symbol) . ' ';
        }
        $sql .= $this->buildKeyClause($driver, $args);

        return $sql;
    }
}
