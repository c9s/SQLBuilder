<?php
use SQLBuilder\Universal\Query\SelectQuery;
use SQLBuilder\Universal\Query\UnionQuery;
use SQLBuilder\Testing\QueryTestCase;
use SQLBuilder\Driver\MySQLDriver;

class UnionQueryTest extends QueryTestCase
{

    public function createDriver() {
        return new MySQLDriver;
    }

    public function testUnionQuery()
    {
        $query1 = new SelectQuery;
        $query1->select(array('name', 'phone', 'address'))
            ->from('contacts');
        $query1->where('name LIKE :name', [ ':name' => '%John%' ]);

        $query2 = new SelectQuery;
        $query2->select(array('name', 'phone', 'address'))
            ->from('users');
        $query2->where('name LIKE :name', [ ':name' => '%Mary%' ]);

        $mainQuery = new UnionQuery;
        $mainQuery->union($query1, $query2);

        $this->assertSql('(SELECT name, phone, address FROM contacts WHERE name LIKE :name) UNION (SELECT name, phone, address FROM users WHERE name LIKE :name)', $mainQuery);
    }
}

