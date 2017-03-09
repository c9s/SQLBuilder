<?php

namespace SQLBuilder\MySQL\Query;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\MySQL\Traits\UserSpecTrait;

/**
 @see http://dev.mysql.com/doc/refman/5.5/en/server-system-variables.html#sysvar_old_passwords
 */
class CreateUserQuery implements ToSqlInterface
{
    use UserSpecTrait;

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $specSql = array();
        foreach ($this->userSpecifications as $spec) {
            $specSql[] = $spec->toSql($driver, $args);
        }

        return 'CREATE USER '.implode(', ', $specSql);
    }
}
