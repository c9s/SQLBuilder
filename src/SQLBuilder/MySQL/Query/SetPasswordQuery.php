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
 * Syntax

    SET PASSWORD [FOR user] =
        {
            PASSWORD('cleartext password')
        | OLD_PASSWORD('cleartext password')
        | 'encrypted password'
        }

 */
class SetPasswordQuery implements ToSqlInterface
{
    public $password;

    public $for;

    public function password($password) {
        $this->password = $password;
        return $this;
    }

    public function _for($userspec) {
        $this->for = UserSpecification::createWithSpec($this, $userspec);
        return $this;
    }

    public function __call($m, $args) {
        if ($m == "for") {
            call_user_func_array(array($this,"_for"), $args);
            return $this;
        }
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = 'SET PASSWORD';
        if ($this->for) {
            $sql .= ' FOR ' . $this->for->getIdentitySql($driver, $args);
        }

        if ($this->password) {
            $sql .= ' = PASSWORD(' . $driver->quote($this->password) . ')';
        }
        $sql .= ';';
        return $sql;
    }
}



