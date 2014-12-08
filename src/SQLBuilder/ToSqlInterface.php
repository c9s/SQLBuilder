<?php
namespace SQLBuilder;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

interface ToSqlInterface { 

    public function toSql(BaseDriver $driver, ArgumentArray $args);

}


