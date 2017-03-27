<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\Exception\UnsupportedDriverException;
use SQLBuilder\ToSqlInterface;

/**
 * Class UUIDQuery
 *
 * @package SQLBuilder\Universal\Query
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class UUIDQuery implements ToSqlInterface
{
    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     * @throws \SQLBuilder\Exception\UnsupportedDriverException
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        if ($driver instanceof MySQLDriver) {
            return 'SELECT UUID();';
        }
        if ($driver instanceof PgSQLDriver) {
            return 'SELECT UUID_GENERATE_V4();';
        }
        if ($driver instanceof SQLiteDriver) {
            return 'SELECT hex(randomblob(16));';
        }
        throw new UnsupportedDriverException($driver, $this);
    }
}
