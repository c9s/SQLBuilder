<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Query\SelectQuery;

class SelectQueryTest extends PHPUnit_Framework_TestCase
{
    public function testSelectQueryWithRawExprAndArgument()
    {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $query = new SelectQuery;
        ok($query);
        $query->select(array('name', 'phone', 'address'))
            ->from('contacts')
            ;
        $query->where('name LIKE :name', [ ':name' => '%John%' ]);
        $sql = $query->toSql($driver, $args);
        is('SELECT name, phone, address FROM contacts WHERE name LIKE :name', $sql);
    }
}

