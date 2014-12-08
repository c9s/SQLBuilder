<?php
use SQLBuilder\RawValue;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Query\InsertQuery;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;

class InsertQueryTest extends PHPUnit_Framework_TestCase
{
    public function testInsertBasic()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $query = new InsertQuery;
        $query->option('LOW_PRIORITY', 'IGNORE');
        $query->insert([ 'name' => 'John', 'confirmed' => true ])->into('users');
        $query->returning('id');
        $sql = $query->toSql($driver, $args);
        is('INSERT LOW_PRIORITY IGNORE INTO users (name,confirmed) VALUES (:p1,:p2)', $sql);
        is('John', $args[':p1'] ); 
        is(true, $args[':p2'] ); 
    }
}

