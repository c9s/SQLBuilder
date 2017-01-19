<?php

namespace SQLBuilder\MySQL\Query;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;

/*
@see http://dev.mysql.com/doc/refman/5.5/en/create-user.html
@see http://dev.mysql.com/doc/refman/5.5/en/server-system-variables.html#sysvar_old_passwords
*/
class ExplainQuery implements ToSqlInterface
{
    protected $query;

    public function __construct(ToSqlInterface $query)
    {
        $this->query = $query;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        return 'EXPLAIN '.$this->query->toSql($driver, $args);
    }
}
