<?php
use SQLBuilder\Raw;
use SQLBuilder\Universal\Query\UpdateQuery;
use SQLBuilder\Universal\Query\DeleteQuery;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Bind;

class DeleteQueryTest extends PDOQueryTestCase
{
    public function testBasicDelete()
    {
        $query = new DeleteQuery;
        $query->delete('users', 'u')
            ->partitions('p1','p2')
            ->where()
                ->equal('id', 3)
                ->is('confirmed', true)
            ;
        $query->limit(1);
        
        $this->assertSqlStatements($query, [ 
            [ new MySQLDriver, 'DELETE users AS u PARTITION (p1,p2) WHERE id = 3 AND confirmed IS TRUE LIMIT 1' ],
            [ new PgSQLDriver, 'DELETE users AS u WHERE id = 3 AND confirmed IS TRUE' ],
            [ new SQLiteDriver, 'DELETE users AS u WHERE id = 3 AND confirmed IS 1' ],
        ]);
    }
}

