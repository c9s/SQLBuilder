<?php
use SQLBuilder\Raw;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Universal\Query\InsertQuery;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;

class InsertQueryTest extends PDOQueryTestCase
{
    public function testCrossPlatformInsert()
    {
        $query = new InsertQuery;
        $query->insert([ 'name' => 'John', 'confirmed' => true ])->into('users');
        $query->returning('id');
        $this->assertSqlStatements($query, [ 
            [ new MySQLDriver, 'INSERT INTO users (name,confirmed) VALUES (:name,:confirmed)' ],
            [ new PgSQLDriver, 'INSERT INTO users (name,confirmed) VALUES (:name,:confirmed) RETURNING id' ],
            [ new SQLiteDriver, 'INSERT INTO users (name,confirmed) VALUES (:name,:confirmed)' ],
        ]);
    }

    public function testInsertBasic()
    {
        $driver = new MySQLDriver;
        $driver->setNamedParamMarker();

        $args = new ArgumentArray;
        $query = new InsertQuery;
        $query->option('LOW_PRIORITY', 'IGNORE');
        $query->insert([ 'name' => 'John', 'confirmed' => true ])->into('users');
        $query->returning('id');
        $sql = $query->toSql($driver, $args);
        is('INSERT LOW_PRIORITY IGNORE INTO users (name,confirmed) VALUES (:name,:confirmed)', $sql);
        is('John', $args[':name'] ); 
        is(true, $args[':confirmed'] ); 
    }


    public function testInsertWithQuestionMark() {

        $driver = new MySQLDriver;
        $driver->setQMarkParamMarker();

        $args = new ArgumentArray;
        $query = new InsertQuery;
        $query->option('LOW_PRIORITY', 'IGNORE');
        $query->insert([ 'name' => 'John', 'confirmed' => true ])->into('users');
        $query->returning('id');
        $sql = $query->toSql($driver, $args);
        is('INSERT LOW_PRIORITY IGNORE INTO users (name,confirmed) VALUES (?,?)', $sql);
    }


}

