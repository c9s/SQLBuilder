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

MYSQL CREATE USER SYNTAX
=========================

CREATE USER user_specification [, user_specification] ...

user_specification:
    user
    [
      | IDENTIFIED WITH auth_plugin [AS 'auth_string']
        IDENTIFIED BY [PASSWORD] 'password'
    ]
 */

class UserSpecification { 

    public $account;

    public $host = 'localhost';

    public $password;

    public $parent;

    public function __construct($parent, $account) {
        $this->parent = $parent;
        $this->account = $account;
    }

    public function host($host) {
        $this->host = $host;
        return $this;
    }

    public function identifiedBy($pass) {
        $this->password = $pass;
        return $this;
    }

    public function getAccount() {
        return $this->account;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getHost() {
        return $this->host;
    }

    public function __call($m , $args) {
        return call_user_func_array(array($this->parent, $m), $args);
    }
}

class CreateUserQuery implements ToSqlInterface
{
    public $userSpecifications = array();

    public function account($account) {
        $user = new UserSpecification($this, $account);
        $this->userSpecifications[] = $user;
        return $user;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $specSql = array();
        foreach($this->userSpecifications as $spec) {
            $sql = $driver->quoteIdentifier($spec->getAccount()) . '@' . $driver->quoteIdentifier($spec->getHost());
            if ($pass = $spec->getPassword()) {
                $sql .= ' IDENTIFIED BY ' . $driver->quote($pass);
            }
            $specSql[] = $sql;
        }
        return 'CREATE USER ' . join(', ', $specSql);
    }
}

