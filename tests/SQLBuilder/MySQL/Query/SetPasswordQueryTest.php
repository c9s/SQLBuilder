<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Testing\QueryTestCase;
use SQLBuilder\MySQL\Query\SetPasswordQuery;

class SetPasswordQueryTest extends QueryTestCase
{

    public function createDriver() {
        return new MySQLDriver;
    }

    public function testSetPassword()
    {
        // SET PASSWORD FOR 'jeffrey'@'localhost' = PASSWORD('cleartext password');
        $q = new SetPasswordQuery;
        $q->password('secret')->for("jeffrey@localhost");
        $this->assertSql('SET PASSWORD FOR `jeffrey`@`localhost` = PASSWORD(\'secret\');', $q);
    }
}

