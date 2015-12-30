<?php
namespace SQLBuilder\MySQL\Query;
use Exception;
use SQLBuilder\Raw;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use SQLBuilder\MySQL\Syntax\UserSpecification;
use SQLBuilder\MySQL\Traits\UserSpecTrait;
use SQLBuilder\Universal\Query\SelectQuery;

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
        return 'EXPLAIN ' . $this->query->toSql($driver, $args);
    }
}

