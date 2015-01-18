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


@see http://dev.mysql.com/doc/refman/5.5/en/set-password.html
@see http://dev.mysql.com/doc/refman/5.0/en/assigning-passwords.html

 */
class SetPasswordQuery implements ToSqlInterface
{
    public $password;

    public $for;

    public $encrypted;

    public function password($password, $encrypted = false) {
        $this->password = $password;
        $this->encrypted = $encrypted;
        return $this;
    }

    public function _for($userspec) {
        $this->for = UserSpecification::createWithFormat($this, $userspec);
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
            if ($this->encrypted) {
                $sql .= ' = ' . $driver->quote($this->password);
            } else {
                $sql .= ' = PASSWORD(' . $driver->quote($this->password) . ')';
            }
        }
        $sql .= ';';
        return $sql;
    }
}



