<?php

namespace SQLBuilder\MySQL\Query;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\MySQL\Syntax\UserSpecification;
use BadMethodCallException;

/**
 * Syntax.
 */
class SetPasswordQuery implements ToSqlInterface
{
    public $password;

    public $for;

    public $encrypted;

    public function password($password, $encrypted = false)
    {
        $this->password = $password;
        $this->encrypted = $encrypted;

        return $this;
    }

    public function _for($userspec)
    {
        $this->for = UserSpecification::createWithFormat($this, $userspec);

        return $this;
    }

    public function __call($m, $args)
    {
        if ($m == 'for') {
            call_user_func_array(array($this, '_for'), $args);

            return $this;
        }
        throw new BadMethodCallException();
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = 'SET PASSWORD';
        if ($this->for) {
            $sql .= ' FOR '.$this->for->getIdentitySql($driver, $args);
        }

        if ($this->password) {
            if ($this->encrypted) {
                $sql .= ' = '.$driver->quote($this->password);
            } else {
                $sql .= ' = PASSWORD('.$driver->quote($this->password).')';
            }
        }
        $sql .= ';';

        return $sql;
    }
}
