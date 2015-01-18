<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\MySQL\Query\CreateUserQuery;
use SQLBuilder\Testing\QueryTestCase;
use SQLBuilder\MySQL\Syntax\UserSpecification;

class UserSpecificationTest extends PHPUnit_Framework_TestCase
{
    public function testCreateWithSpec()
    {
        $spec = UserSpecification::createWithFormat(NULL, 'localuser@localhost');
        $this->assertInstanceOf('SQLBuilder\MySQL\Syntax\UserSpecification', $spec);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateWithWrongFormat()
    {
        UserSpecification::createWithFormat(NULL, 'localuser_localhost');
    }
}

