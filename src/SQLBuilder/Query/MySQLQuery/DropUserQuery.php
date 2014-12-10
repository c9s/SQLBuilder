<?php
namespace SQLBuilder\Query\MySQLQuery;
use Exception;
use SQLBuilder\RawValue;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;


/**
 *
 * @see http://dev.mysql.com/doc/refman/5.5/en/drop-user.html
 */
class DropUserQuery
{

    public $userSpecifications = array();

    public function user($spec = NULL) {
        $user = new UserSpecification($this);
        $this->userSpecifications[] = $user;
        if (is_string($spec)) {
            list($account, $host) = explode('@', $spec);
            $user->account(trim($account, "`'"));
            $user->host(trim($host, "`'"));
        }
        return $user;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $specSql = array();
        foreach($this->userSpecifications as $spec) {
            $sql = $driver->quoteIdentifier($spec->getAccount()) . '@' . $driver->quoteIdentifier($spec->getHost());
            $specSql[] = $sql;
        }
        return 'DROP USER ' . join(', ', $specSql);
    }


}




