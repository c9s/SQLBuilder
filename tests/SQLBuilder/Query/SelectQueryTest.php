<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Query\SelectQuery;

class SelectQueryTest extends PHPUnit_Framework_TestCase
{
    public function testSelectQuery()
    {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $select = new SelectQuery;
        ok($select);


        
    }
}

