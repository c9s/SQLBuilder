<?php

namespace SQLBuilder\MySQL\Query;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\MySQL\Traits\UserSpecTrait;

/**
 * @see http://dev.mysql.com/doc/refman/5.5/en/drop-user.html
 */
class DropUserQuery
{
    use UserSpecTrait;

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $specSql = array();
        foreach ($this->userSpecifications as $spec) {
            $specSql[] = $spec->getIdentitySql($driver, $args);
        }

        return 'DROP USER '.implode(', ', $specSql);
    }
}
