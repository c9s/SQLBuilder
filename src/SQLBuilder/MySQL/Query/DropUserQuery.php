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

/**
 *
 * @see http://dev.mysql.com/doc/refman/5.5/en/drop-user.html
 */
class DropUserQuery
{
    use UserSpecTrait;

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $specSql = array();
        foreach($this->userSpecifications as $spec) {
            $sql = $driver->quoteIdentifier($spec->getAccount()) . '@' . $driver->quoteIdentifier($spec->getHost());
            $specSql[] = $sql;
        }
        return 'DROP USER ' . join(', ', $specSql);
    }
}




