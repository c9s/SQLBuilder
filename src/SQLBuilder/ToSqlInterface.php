<?php
namespace SQLBuilder;
use SQLBuilder\Driver\BaseDriver;

interface ToSqlInterface { 

    public function toSql(BaseDriver $driver);

}


