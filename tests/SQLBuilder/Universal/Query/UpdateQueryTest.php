<?php
use SQLBuilder\Raw;
use SQLBuilder\Universal\Query\UpdateQuery;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\ParamMarker;
use SQLBuilder\Bind;
use SQLBuilder\Testing\PDOQueryTestCase;

class UpdateQueryTest extends PDOQueryTestCase
{

    public function testUpdateMultipleTables() {
        $query = new UpdateQuery;
        $query->update('users', 'u');
        $query->update('users2', 'u2');
        $query->set([ 'name' => 'Mary', 'phone' => '09752222123' ]);
        $query->where()->equal('id', 3);
        $this->assertSqlStatements($query, [ 
            [ new MySQLDriver, 'UPDATE users AS u, users2 AS u2 SET name = :name, phone = :phone WHERE id = 3' ],
        ]);
    }



    public function testCrossPlatformBasicUpdate() {
        $query = new UpdateQuery;
        $query->update('users')
            ->partitions('p1','p2')
            ->join('user_votes')->as('uv')
                ->left()
                ->on('uv.user_id = u.id');

        $query->set([ 'name' => 'Mary', 'phone' => '09752222123' ]);
        $query->where()->equal('id', 3);
        $query->limit(1);

        $this->assertSqlStatements($query, [ 
            [ new MySQLDriver, 'UPDATE users LEFT JOIN user_votes AS uv ON (uv.user_id = u.id) PARTITION (p1,p2) SET name = :name, phone = :phone WHERE id = 3 LIMIT 1' ],
            [ new PgSQLDriver, 'UPDATE users LEFT JOIN user_votes AS uv ON (uv.user_id = u.id) SET name = :name, phone = :phone WHERE id = 3' ],
            [ new SQLiteDriver, 'UPDATE users LEFT JOIN user_votes AS uv ON (uv.user_id = u.id) SET name = :name, phone = :phone WHERE id = 3' ],
        ]);
    }

    public function testMySQLBasicUpdate()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $query = new UpdateQuery;
        $query->update('users')->set([ 
            'name' => 'Mary'
        ]);
        $query->where()
            ->equal('id', 3);
        ok($query);
        $sql = $query->toSql($driver, $args);
        is('UPDATE users SET name = :name WHERE id = 3', $sql);
    }


    public function testBasicUpdateWithBind()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $query = new UpdateQuery;
        $query->options('LOW_PRIORITY', 'IGNORE')->update('users')->set([ 
            'name' => new Bind('nameA','Mary'),
        ]);
        $query->where()
            ->equal('id', new Bind('id', 3));
        ok($query);
        $sql = $query->toSql($driver, $args);
        is('UPDATE LOW_PRIORITY IGNORE users SET name = :nameA WHERE id = :id', $sql);
    }

    public function testBasicUpdateWithParamMarker()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $query = new UpdateQuery;
        $query->options('LOW_PRIORITY', 'IGNORE')->update('users')->set([ 
            'name' => new ParamMarker('Mary'),
        ]);
        $query->where()
            ->equal('id', new ParamMarker(3));
        ok($query);
        $sql = $query->toSql($driver, $args);
        is('UPDATE LOW_PRIORITY IGNORE users SET name = ? WHERE id = ?', $sql);
    }


}

