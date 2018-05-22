<?php

namespace SQLBuilder\Universal\Query;

use Exception;
use SQLBuilder\Exception\UnsupportedDriverException;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Syntax\Conditions;
use SQLBuilder\Universal\Traits\OrderByTrait;
use SQLBuilder\Universal\Traits\WhereTrait;
use SQLBuilder\Universal\Traits\PagingTrait;
use SQLBuilder\Universal\Expr\SelectExpr;
use SQLBuilder\MySQL\Traits\PartitionTrait;
use SQLBuilder\MySQL\Traits\IndexHintTrait;
use SQLBuilder\Universal\Traits\JoinTrait;
use SQLBuilder\Universal\Traits\OptionTrait;

class UUIDQuery implements ToSqlInterface
{
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        if ($driver instanceof MySQLDriver) {
            return 'SELECT UUID();';
        }
        if ($driver instanceof PgSQLDriver) {
            return 'SELECT UUID_GENERATE_V4();';
        }
        if ($driver instanceof SQLiteDriver) {
            return 'SELECT SUBSTR(u, 1, 8)||'-'||SUBSTR(u, 9, 4)||'-4'||SUBSTR(u, 13, 3)|| '-'||v||SUBSTR(u, 17, 3)||'-'||SUBSTR(u, 21, 12) as uuid from (select LOWER(HEX(RANDOMBLOB(16))) as u, SUBSTR('89ab', ABS(RANDOM()) % 4 + 1, 1) as v);';
        }
        throw new UnsupportedDriverException($driver, $this);
    }
}
