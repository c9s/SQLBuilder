<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Universal\Query\CreateDatabaseQuery;
use SQLBuilder\Universal\Query\DropDatabaseQuery;

class CreateDatabaseQueryTest extends PDOQueryTestCase
{
    public $driverType = 'MySQL';

    public function createDriver() {
        return new MySQLDriver;
    }

    public function testQuery() {
        $q = new DropDatabaseQuery('test123123');
        $q->ifExists();
        $this->assertQuery($q);

        $q = new CreateDatabaseQuery('test123123');
        $q->characterSet('utf8');
        $this->assertSql("CREATE DATABASE `test123123` CHARACTER SET 'utf8'", $q);
        $this->assertQuery($q);

        $q = new DropDatabaseQuery('test123123');
        $this->assertSql("DROP DATABASE `test123123`", $q);
        $this->assertQuery($q);
    }

    public function testCreateDatabaseQuery() {
        $q = new DropDatabaseQuery('test_db');
        $q->ifExists();

        // $this->assertDriverQuery(new PgSQLDriver, $q);
        $this->assertDriverQuery(new MySQLDriver, $q);

        $q = new CreateDatabaseQuery;
        $q->create('test_db')
            ->characterSet('utf8')
            ->collate('en_US.UTF-8')
            ->owner('postgres')
            ->ctype('en_US.UTF-8')
            ->template('template0')
            ->encoding('UTF8')
            ->tablespace('pg_default')
            ->connectionLimit(3)
            ;
        $this->assertSqlStrings($q, [
            [ new MySQLDriver, "CREATE DATABASE `test_db` CHARACTER SET 'utf8' COLLATE 'en_US.UTF-8'"],
            //[ new PgSQLDriver, 'CREATE DATABASE "test_db" OWNER \'postgres\' TEMPLATE \'template0\' ENCODING \'UTF8\' LC_COLLATE \'en_US.UTF-8\' LC_CTYPE \'en_US.UTF-8\' TABLESPACE \'pg_default\' CONNECTION LIMIT 3'],
        ]);

        //$this->assertDriverQuery(new PgSQLDriver, $q);
        $this->assertDriverQuery(new MySQLDriver, $q);
    }


}

