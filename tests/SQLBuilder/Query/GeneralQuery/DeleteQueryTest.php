<?php
use SQLBuilder\RawValue;
use SQLBuilder\Query\GeneralQuery\UpdateQuery;
use SQLBuilder\Query\GeneralQuery\DeleteQuery;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;

class DeleteQueryTest extends PHPUnit_Framework_TestCase
{
    public function testDelete()
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;
        $query = new DeleteQuery;
        $query->delete('users', 'u')->where()
            ->equal('id', 3);
        ok($query);
        $sql = $query->toSql($driver, $args);
        is('DELETE users AS u WHERE id = 3', $sql);
    }
}

