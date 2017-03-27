<?php

namespace SQLBuilder;

use SQLBuilder\Driver\BaseDriver;

/**
 * Interface ToSqlInterface
 * @package SQLBuilder
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 */
interface ToSqlInterface
{
    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args);
}
