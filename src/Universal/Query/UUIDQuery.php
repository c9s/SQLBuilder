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
            return 'SELECT hex(randomblob(16));';
        }
        throw new UnsupportedDriverException($driver, $this);
    }
}
