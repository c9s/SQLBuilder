<?php

use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Universal\Syntax\Conditions;
use SQLBuilder\Universal\Expr\RawExpr;
use SQLBuilder\Criteria;
use SQLBuilder\ArgumentArray;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\Bind;
use SQLBuilder\Raw;
use SQLBuilder\Testing\QueryTestCase;

class RawExprTest extends QueryTestCase
{
    public function testConstructor() {
        $expr = new RawExpr('NOW()');
        $this->assertSqlStrings($expr,[
            [new MySQLDriver, "NOW()"],
        ]);
    }
}
