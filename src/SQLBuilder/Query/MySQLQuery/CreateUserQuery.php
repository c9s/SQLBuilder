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

/**

MYSQL CREATE USER SYNTAX
=========================

CREATE USER user_specification [, user_specification] ...

user_specification:
    user
    [
      | IDENTIFIED WITH auth_plugin [AS 'auth_string']
        IDENTIFIED BY [PASSWORD] 'password'
    ]


When using auth plugin, we need to specify the password later.

The 'old_passwords' global variable is for the hash algorithm.

There are two mysql auth plugin:
    mysql_native_password
    mysql_old_password

CREATE USER 'jeffrey'@'localhost' IDENTIFIED WITH mysql_native_password;
SET old_passwords = 0;
SET PASSWORD FOR 'jeffrey'@'localhost' = PASSWORD('mypass');


@see http://dev.mysql.com/doc/refman/5.5/en/create-user.html
@see http://dev.mysql.com/doc/refman/5.5/en/server-system-variables.html#sysvar_old_passwords
*/
class CreateUserQuery implements ToSqlInterface
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
            if ($pass = $spec->getPassword()) {
                $sql .= ' IDENTIFIED BY';
                if ($spec->passwordByHash) {
                    $sql .= ' PASSWORD';
                }
                $sql .= ' ' . $driver->quote($pass);
            }
            elseif ($authPlugin = $spec->getAuthPlugin()) {
                $sql .= ' IDENTIFIED WITH ' . $driver->quoteIdentifier($authPlugin);
            }
            $specSql[] = $sql;
        }
        return 'CREATE USER ' . join(', ', $specSql);
    }
}

