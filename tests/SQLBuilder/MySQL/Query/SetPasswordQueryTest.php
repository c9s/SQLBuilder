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

    public function testSetPasswordEncrypted()
    {
        $q = new SetPasswordQuery;
        $q->password('d8e8fca2dc0f896fd7cb4cb0031ba249', true)->for("jeffrey@localhost");
        $this->assertSql("SET PASSWORD FOR `jeffrey`@`localhost` = 'd8e8fca2dc0f896fd7cb4cb0031ba249';", $q);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testSetPasswordBadMethodCall()
    {
        $q = new SetPasswordQuery;
        $q->password('d8e8fca2dc0f896fd7cb4cb0031ba249', true)->for("jeffrey@localhost");
        $q->foo();
    }
}

