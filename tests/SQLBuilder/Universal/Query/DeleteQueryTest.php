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

    public function testDeleteWithOr()
    {
        $query = new DeleteQuery;
        $query->delete('users', 'u')
            ->partitions('p1','p2')
            ->where()
                ->equal('id', 3)
                ->or()
                ->equal('id', 4)
            ;
        $query->limit(1);
        
        $this->assertSqlStatements($query, [ 
            [ new MySQLDriver, 'DELETE FROM users AS u PARTITION (p1,p2) WHERE id = 3 OR id = 4 LIMIT 1' ],
            [ new PgSQLDriver, 'DELETE FROM users AS u WHERE id = 3 OR id = 4' ],
            [ new SQLiteDriver, 'DELETE FROM users AS u WHERE id = 3 OR id = 4' ],
        ]);

        is(3, $query->where()->count());
    }

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
            [ new MySQLDriver, 'DELETE FROM users AS u PARTITION (p1,p2) WHERE id = 3 AND confirmed IS TRUE LIMIT 1' ],
            [ new PgSQLDriver, 'DELETE FROM users AS u WHERE id = 3 AND confirmed IS TRUE' ],
            [ new SQLiteDriver, 'DELETE FROM users AS u WHERE id = 3 AND confirmed IS 1' ],
        ]);
    }
}

