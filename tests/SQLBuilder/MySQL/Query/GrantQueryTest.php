<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\MySQL\Query\CreateUserQuery;
use SQLBuilder\MySQL\Query\GrantQuery;

class GrantQueryTest extends PHPUnit_Framework_TestCase
{
    public function testGrantQuery()
    {
        return;
        $driver = new MySQLDriver;
        $args = new ArgumentArray;

        // GRANT ALL ON db1.* TO 'jeffrey'@'localhost';
        $q = new GrantQuery;
        $q->grant('ALL')->on('db1.*')->to('jeffrey@localhost');


        // GRANT SELECT (col1), INSERT (col1,col2) ON mydb.mytbl TO 'someuser'@'somehost';
        $q = new GrantQuery;
        $q->grant('SELECT', ['col1'])
            ->grant('INSERT', ['col1','col2'])
            ->on('mydb.mytbl')
            ->to('someuser@somehost');

        // GRANT EXECUTE ON PROCEDURE mydb.myproc TO 'someuser'@'somehost';
        $q = new GrantQuery;
        $q->grant('EXECUTE')
            ->of('PROCEDURE')
            ->on('mydb.mytbl')
            ->to('someuser@somehost');

        $q = new GrantQuery;
        $q->grant('EXECUTE')
            ->on('mydb.mytbl', 'PROCEDURE')
            ->to('someuser@somehost');

        // GRANT OPTION
        // GRANT USAGE ON *.* TO ...  WITH MAX_QUERIES_PER_HOUR 500 MAX_UPDATES_PER_HOUR 100;
        $q = new GrantQuery;
        $q->grant('USAGE')
            ->on('*.*')
            ->to('someuser@somehost')
            ->with('GRANT OPTION')
            ->with('MAX_QUERIES_PER_HOUR', 100)
            ->with('MAX_CONNECTIONS_PER_HOUR', 100)
            ;
    }
}

