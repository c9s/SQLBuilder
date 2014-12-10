<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;

class RightJoin extends Join implements ToSqlInterface
{
    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        return ' RIGHT' . parent::toSql($driver, $args);
    }
}
