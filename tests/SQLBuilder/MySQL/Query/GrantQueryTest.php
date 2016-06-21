<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\MySQL\Query\CreateUserQuery;
use SQLBuilder\MySQL\Query\GrantQuery;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Testing\QueryTestCase;
use SQLBuilder\MySQL\Syntax\UserSpecification;

class GrantQueryTest extends QueryTestCase
{
    public $driverType = 'MySQL';

    public function createDriver() {
        return new MySQLDriver;
    }

    public function testBasicGrantQuery()
    {
        // GRANT ALL ON db1.* TO 'jeffrey'@'localhost';
        $q = new GrantQuery;
        $q->grant('ALL')->on('testing.*')
            ->to('jeffrey@localhost');
        $this->assertSql('GRANT ALL ON testing.* TO `jeffrey`@`localhost`', $q);
        # $this->assertQuery($q);
    }

    public function testGrantToWithUserSpec()
    {
        $specOn = UserSpecification::createWithFormat(NULL, 'localuser@localhost');
        $specTo = UserSpecification::createWithFormat(NULL, 'externaluser@somehost');
        $q = new GrantQuery;
        $q->grant('PROXY')->on($specOn)
            ->to($specTo);
        $this->assertSql('GRANT PROXY ON `localuser`@`localhost` TO `externaluser`@`somehost`', $q);
    }

    public function testGrantProxy()
    {
        // GRANT PROXY ON 'localuser'@'localhost' TO 'externaluser'@'somehost';
        $q = new GrantQuery;
        $q->grant('PROXY')->on('localuser@localhost')
            ->to('externaluser@somehost');
        $this->assertSql('GRANT PROXY ON `localuser`@`localhost` TO `externaluser`@`somehost`', $q);
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


    /**
     * @expectedException InvalidArgumentException
     */
    public function testIncompleteSettings()
    {
        $q = new GrantQuery;
        $q->on(false);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testIncompleteSettings2()
    {
        $q = new GrantQuery;
        $q->to(false);
    }



    public function testGrantExecuteOnProcedure() 
    {
        $driver = new MySQLDriver;
        $args = new ArgumentArray;

        // GRANT EXECUTE ON PROCEDURE mydb.myproc TO 'someuser'@'somehost';
        $q = new GrantQuery;
        $q->grant('EXECUTE')
            ->on('mydb.mytbl','PROCEDURE')
            ->to('someuser@somehost');
        $this->assertSql('GRANT EXECUTE ON PROCEDURE mydb.mytbl TO `someuser`@`somehost`', $q);
    }

    public function testGrantExecuteOnProcedure2() 
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

