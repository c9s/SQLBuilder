<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Universal\Query\CreateTableQuery;
use SQLBuilder\Universal\Query\DeleteQuery;
use SQLBuilder\Universal\Query\DropTableQuery;

class DeleteQueryTest extends PDOQueryTestCase
{

    public $driverType = 'MySQL';

    public function createDriver() {
        return new MySQLDriver;
    }


    public function setUp()
    {
        parent::setUp();

        $q = new DropTableQuery('users');
        $q->ifExists();
        $this->assertQuery($q);

        $q = new CreateTableQuery('users');
        $q->column('id')->integer()
            ->primary()
            ->autoIncrement();
        $q->column('first_name')->varchar(32);
        $q->column('last_name')->varchar(16);
        $q->column('age')->tinyint(3)->unsigned()->null();
        $q->column('phone')->varchar(24)->null();
        $q->column('email')->varchar(128)->notNull();
        $q->column('confirmed')->boolean()->default(false);
        $q->column('types')->set('student', 'teacher');
        $q->column('remark')->text();
        $q->index([ 'first_name', 'last_name' ])->name('username_idx');
        $this->assertQuery($q);
    }


    /**
     * @expectedException SQLBuilder\Exception\IncompleteSettingsException
     */
    public function testDeleteWithoutTable()
    {
        $q = new DeleteQuery;
        $this->assertQuery($q);
    }


    public function testQueryClone()
    {
        $q = new DeleteQuery;
        $q->delete('users');
        $q->where()
            ->equal('id', 3);
        $this->assertQuery($q);

        $q2 = clone $q;
        $this->assertSqlStrings($q, [ 
            [new MySQLDriver, 'DELETE FROM users WHERE id = 3'],
        ]);
    }

    public function testDeleteWithoutAlias()
    {
        $q = new DeleteQuery;
        $q->delete('users');
        $this->assertQuery($q);
    }

    public function testDeleteWithIndexHint()
    {
        $q = new DeleteQuery;
        $q->delete('users');
        $q->orderBy('first_name', 'DESC');
        $this->assertSqlStrings($q, [
            [ new MySQLDriver, 'DELETE FROM users ORDER BY first_name DESC' ],
        ]);
        $this->assertQuery($q);
    }

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
        $this->assertSqlStrings($query, [
            [ new MySQLDriver, 'DELETE FROM users AS u PARTITION (p1,p2) WHERE id = 3 OR id = 4 LIMIT 1' ],
            //[ new PgSQLDriver, 'DELETE FROM users AS u WHERE id = 3 OR id = 4' ],
            [ new SQLiteDriver, 'DELETE FROM users AS u WHERE id = 3 OR id = 4' ],
        ]);
        $this->assertEquals(3, $query->where()->count());
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
        
        $this->assertSqlStrings($query, [
            [ new MySQLDriver, 'DELETE FROM users AS u PARTITION (p1,p2) WHERE id = 3 AND confirmed IS TRUE LIMIT 1' ],
            //[ new PgSQLDriver, 'DELETE FROM users AS u WHERE id = 3 AND confirmed IS TRUE' ],
            [ new SQLiteDriver, 'DELETE FROM users AS u WHERE id = 3 AND confirmed IS 1' ],
        ]);
    }
}

