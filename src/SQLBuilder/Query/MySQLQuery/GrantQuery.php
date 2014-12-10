<?php
namespace SQLBuilder\Query\MySQLQuery;
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
use SQLBuilder\Syntax\UserSpecification;
use SQLBuilder\Traits\UserSpecTrait;

class GrantQuery implements ToSqlInterface
{
    use UserSpecTrait;

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        return '';
    }
}



