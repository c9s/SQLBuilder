<?php
use SQLBuilder\Universal\Query\CreateTableQuery;
use SQLBuilder\Universal\Query\DropTableQuery;
use SQLBuilder\Universal\Query\AlterTableQuery;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Raw;
use SQLBuilder\Universal\Syntax\Column;
use SQLBuilder\Universal\Syntax\Distinct;
use SQLBuilder\Universal\Expr\FuncCallExpr;

class DistinctTest extends PDOQueryTestCase
{
    public function test()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $expr = new Distinct(new FuncCallExpr('SUM', [ new Raw('*')]));
        $sql = $expr->toSql($driver, $args);
        is('DISTINCT SUM(*)', $sql);
    }
}

