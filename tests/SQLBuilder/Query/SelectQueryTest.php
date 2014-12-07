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

    public function testSimpleJoin() {
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
        is('SELECT id, name, phone, address FROM users AS u JOIN posts AS p ON (p.user_id = u.id) WHERE u.name LIKE :name', $sql);
    }

    public function testOrderBy()
    {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $query = new SelectQuery;
        $query->select(array('id', 'name', 'phone', 'address'))
            ->from(array('users' => 'u'))
            ->orderBy('rand()')
            ->orderBy('id', 'DESC')
            ;
        $sql = $query->toSql($driver, $args);
        is('SELECT id, name, phone, address FROM users AS u ORDER BY rand(), id DESC', $sql);
    }


    public function testGroupBy()
    {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $query = new SelectQuery;
        $query->select(array('id', 'country', 'code'))
            ->from(array('counties' => 'c'))
            ->groupBy('c.code')
            ;
        $sql = $query->toSql($driver, $args);
        is('SELECT id, country, code FROM counties AS c GROUP BY c.code', $sql);
    }

    public function testGroupByWithRollUp()
    {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $query = new SelectQuery;
        $query->select(array('id', 'country', 'code'))
            ->from(array('counties' => 'c'))
            ->groupBy('c.code', [ 'WITH ROLLUP' ])
            ;
        $sql = $query->toSql($driver, $args);
        is('SELECT id, country, code FROM counties AS c GROUP BY c.code WITH ROLLUP', $sql);
    }


    public function testMultipleJoin() {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $query = new SelectQuery;
        ok($query);
        $query->select(array('id', 'name', 'phone', 'address'))
            ->from(array('users' => 'u'))
            ;

        $query->join('posts')
                ->as('p')
                ->left()
                ->on('p.user_id = u.id')
                ;

        $query->join('ratings')
                ->as('r')
                ->left()
                ->on('r.user_id = u.id')
                ;

        $sql = $query->toSql($driver, $args);
        is('SELECT id, name, phone, address FROM users AS u LEFT JOIN posts AS p ON (p.user_id = u.id) LEFT JOIN ratings AS r ON (r.user_id = u.id)', $sql);
    }
}

