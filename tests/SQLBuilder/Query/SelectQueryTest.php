<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Query\SelectQuery;

class SelectQueryTest extends PHPUnit_Framework_TestCase
{
    public function testRawExprAndArgument()
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

    public function testJoin() {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $query = new SelectQuery;
        ok($query);
        $query->select(array('id', 'name', 'phone', 'address'))
            ->from(array('users' => 'u'))
            ->join('posts')
                ->as('p')
                ->on('p.user_id = u.id')
            ;
        $query->where('u.name LIKE :name', [ ':name' => '%John%' ]);
        $sql = $query->toSql($driver, $args);
        is('SELECT id, name, phone, address FROM users AS u JOIN posts AS p WHERE u.name LIKE :name', $sql);

    }
}

