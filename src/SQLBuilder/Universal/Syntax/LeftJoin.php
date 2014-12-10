<?php
namespace SQLBuilder\Universal\Syntax;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;

class LeftJoin extends Join implements ToSqlInterface
{
    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        return ' LEFT' . parent::toSql($driver, $args);
    }
}
