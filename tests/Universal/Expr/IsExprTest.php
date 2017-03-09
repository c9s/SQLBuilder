<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Universal\Syntax\Conditions;
use SQLBuilder\Universal\Expr\IsExpr;
use SQLBuilder\Criteria;
use SQLBuilder\ArgumentArray;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\Bind;
use SQLBuilder\Raw;
use SQLBuilder\Testing\QueryTestCase;

class IsExprTest extends QueryTestCase
{

    public function testConstructor() {
        $expr = new IsExpr('a', true);
        $this->assertSqlStrings($expr,[
            [new MySQLDriver,'a IS TRUE'],
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorInvalidType() {
        $expr = new IsExpr('a', 'blah');
        $this->assertSqlStrings($expr,[
            [new MySQLDriver,'a IS TRUE'],
        ]);
    }

}
