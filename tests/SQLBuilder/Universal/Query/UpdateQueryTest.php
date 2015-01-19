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

    /**
     * @expectedException SQLBuilder\Exception\IncompleteSettingsException
     */
    public function testUpdateWithoutTable()
    {
        $query = new UpdateQuery;
        $this->assertSqlStrings($query,[ 
            [new MySQLDriver, '']
        ]);
    }

    public function testUpdateTableWithIndexHintOnAlias()
    {
        $query = new UpdateQuery;
        $query->update('users', 'u');
        $query->update('users2', 'u2');
        $query->set([ 'name' => new Bind('name','Mary'), 'phone' => new Bind('phone','09752222123') ]);
        $query->indexHint('u')->useIndex('users_idx')->forOrderBy();
        $query->indexHint('u2')->useIndex('users_idx')->forOrderBy();
        $this->assertSqlStrings($query, [ 
            [ new MySQLDriver, 'UPDATE users AS u USE INDEX FOR ORDER BY (users_idx), users2 AS u2 USE INDEX FOR ORDER BY (users_idx) SET name = :name, phone = :phone' ],
            [ new PgSQLDriver, 'UPDATE users AS u, users2 AS u2 SET name = :name, phone = :phone' ],
        ]);
    }

    public function testUpdateTableWithIndexHintOn2TableNames()
    {
        $query = new UpdateQuery;
        $query->update('users','u')->update('users2','u2');
        $query->set([ 'name' => new Bind('name','Mary'), 'phone' => new Bind('phone','09752222123') ]);
        $query->indexHint('users')->useIndex('users_idx')->forOrderBy();
        $this->assertSqlStrings($query, [ 
            [ new MySQLDriver, 'UPDATE users AS u USE INDEX FOR ORDER BY (users_idx), users2 AS u2 SET name = :name, phone = :phone' ],
            [ new PgSQLDriver, 'UPDATE users AS u, users2 AS u2 SET name = :name, phone = :phone' ],
        ]);
    }

    public function testUpdateTableWithIndexHintOnTableNames()
    {
        $query = new UpdateQuery;
        $query->update('users');
        $query->update('users2');
        $query->set([ 'name' => new Bind('name','Mary'), 'phone' => new Bind('phone','09752222123') ]);
        $query->indexHint('users')->useIndex('users_idx')->forOrderBy();
        $this->assertSqlStrings($query, [ 
            [ new MySQLDriver, 'UPDATE users USE INDEX FOR ORDER BY (users_idx), users2 SET name = :name, phone = :phone' ],
            [ new PgSQLDriver, 'UPDATE users, users2 SET name = :name, phone = :phone' ],
        ]);
    }

    public function testUpdateMultipleTables() {
        $query = new UpdateQuery;
        $query->update('users', 'u');
        $query->update('users2', 'u2');
        $query->set([ 'name' => new Bind('name','Mary'), 'phone' => new Bind('phone','09752222123') ]);
        $query->where()->equal('id', 3);
        $this->assertSqlStrings($query, [ 
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

        $query->set([ 'name' => new Bind('name','Mary'), 'phone' => new Bind('phone','09752222123') ]);
        $query->where()->equal('id', 3);
        $query->limit(1);

        $this->assertSqlStrings($query, [ 
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
            'name' => new Bind('name','Mary'),
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

