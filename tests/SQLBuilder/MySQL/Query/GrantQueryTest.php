<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\MySQL\Query\CreateUserQuery;
use SQLBuilder\MySQL\Query\GrantQuery;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Testing\QueryTestCase;

class GrantQueryTest extends QueryTestCase
{

    public function createDriver() {
        return new MySQLDriver;
    }


    public function testBasicGrantQuery()
    {
        // GRANT ALL ON db1.* TO 'jeffrey'@'localhost';
        $q = new GrantQuery;
        $q->grant('ALL')->on('db1.*')
            ->to('jeffrey@localhost');
        $this->assertSql('GRANT ALL ON db1.* TO `jeffrey`@`localhost`', $q);
    }

    public function testGrantPrivWithColumns() 
    {
        // GRANT SELECT (col1), INSERT (col1,col2) ON mydb.mytbl TO 'someuser'@'somehost';
        $q = new GrantQuery;
        $q->grant('SELECT', ['col1'])
            ->grant('INSERT', ['col1','col2'])
            ->on('mydb.mytbl')
            ->to('someuser@somehost');

        $this->assertSql('GRANT SELECT (col1), INSERT (col1,col2) ON mydb.mytbl TO `someuser`@`somehost`', $q);
    }

    public function testGrantExecuteOnProcedure() 
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;

        // GRANT EXECUTE ON PROCEDURE mydb.myproc TO 'someuser'@'somehost';
        $q = new GrantQuery;
        $q->grant('EXECUTE')
            ->of('PROCEDURE')
            ->on('mydb.mytbl')
            ->to('someuser@somehost');
        $this->assertSql('GRANT EXECUTE ON PROCEDURE mydb.mytbl TO `someuser`@`somehost`', $q);
    }

    public function testGrantWithGrantOptions()
    {
        // GRANT USAGE ON *.* TO ...  WITH MAX_QUERIES_PER_HOUR 500 MAX_UPDATES_PER_HOUR 100;
        $q = new GrantQuery;
        $q->grant('USAGE')
            ->on('*.*')
            ->to('someuser@somehost')
            ->with('MAX_QUERIES_PER_HOUR', 100)
            ->with('MAX_CONNECTIONS_PER_HOUR', 100)
            ;
        $this->assertSql('GRANT USAGE ON *.* TO `someuser`@`somehost` WITH MAX_QUERIES_PER_HOUR 100 MAX_CONNECTIONS_PER_HOUR 100', $q);
    }


}

