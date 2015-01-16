<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\MySQL\Query\ExplainQuery;
use SQLBuilder\Testing\QueryTestCase;
use SQLBuilder\Testing\PDOQueryTestCase;
use SQLBuilder\Universal\Query\SelectQuery;
use SQLBuilder\Universal\Expr\FuncCallExpr;
use SQLBuilder\Universal\Query\CreateTableQuery;
use SQLBuilder\Universal\Query\DropTableQuery;
use SQLBuilder\Bind;

class SelectQueryTest extends PDOQueryTestCase
{
    public $driverType = 'MySQL';

    public function createDriver()
    {
        return new MySQLDriver;
    }

    public function setUp()
    {
        parent::setUp();

        $q = new CreateTableQuery('products');
        $q->column('id')->integer()
            ->primary()
            ->autoIncrement();
        $q->column('name')->varchar(32);
        $q->column('sn')->varchar(16);
        $q->column('price')->int(4)->unsigned();
        $q->column('content')->text();
        $this->assertQuery($q);
    }

    public function tearDown()
    {
        parent::tearDown();
        $q = new DropTableQuery('products');
        $q->IfExists();
        $this->assertQuery($q);
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidWhereExpr()
    {
        $query = new SelectQuery;
        $query->select(array('id', 'name', 'sn', 'content'))
            ->from('products');
        $query->where(TRUE);
    }

    public function testRawExprAndArgument()
    {
        $query = new SelectQuery;
        $query->select(array('id', 'name', 'sn', 'content'))
            ->from('products');
        $query->where('name LIKE :name', [ ':name' => new Bind('name','%John%') ]);
        $this->assertSql('SELECT id, name, sn, content FROM products WHERE name LIKE :name', $query);
        $this->assertQuery($query);
    }


    public function testSimpleJoin() {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $query = new SelectQuery;
        ok($query);
        $query->select(array('id', 'name', 'phone', 'address'))
            ->from('users', 'u')
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
            ->from('users', 'u')
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
            ->from('counties', 'c')
            ->groupBy('c.code')
            ;
        $sql = $query->toSql($driver, $args);
        is('SELECT id, country, code FROM counties AS c GROUP BY c.code', $sql);
    }

    public function testSelectOptions() 
    {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $query = new SelectQuery;
        $query->select(array('id', 'country', 'code'))
            ->distinct()
            ->option('SQL_SMALL_RESULT')
            ->from('counties', 'c')
            ;
        $sql = $query->toSql($driver, $args);
        is('SELECT DISTINCT id, country, code FROM counties AS c', $sql);
    }

    public function testGroupByWithRollUp()
    {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $query = new SelectQuery;
        $query->select(array('id', 'country', 'code'))
            ->from('counties', 'c')
            ->groupBy('c.code', [ 'WITH ROLLUP' ])
            ;
        $sql = $query->toSql($driver, $args);
        is('SELECT id, country, code FROM counties AS c GROUP BY c.code WITH ROLLUP', $sql);
    }

    public function testSelectWithSharedLock() {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $query = new SelectQuery;
        ok($query);
        $query->select(array('id', 'name', 'phone', 'address'))
            ->from('users', 'u')
            ->where('name = :name', [ ':name' => 'Joan' ])
            ;
        $query->lockInShareMode();
        $sql = $query->toSql($driver, $args);
        is('SELECT id, name, phone, address FROM users AS u WHERE name = :name LOCK IN SHARE MODE', $sql);

        $query->forUpdate();
        $sql = $query->toSql($driver, $args);
        is('SELECT id, name, phone, address FROM users AS u WHERE name = :name FOR UPDATE', $sql);
    }

    public function testSelectWithOrderByClear()
    {
        $q = new SelectQuery;
        $q->select(array('name'))
            ->from('products');
        $q->orderBy('name', 'ASC');
        $q->clearOrderBy();
        $this->assertSqlStatements($q, [[new MySQLDriver, 'SELECT name FROM products']]);

    }

    public function testSelectWithMultipleOrderBy()
    {
        $q = new SelectQuery;
        $q->select(array('name'))
            ->from('products');
        $q->orderBy('name', 'ASC');
        $q->orderBy('phone', 'ASC');
        $this->assertSqlStatements($q, [[new MySQLDriver, 'SELECT name FROM products ORDER BY name ASC, phone ASC']]);
    }

    public function testSelectWithOrderByFuncExpr()
    {
        $q = new SelectQuery;
        $q->select(array('name'))
            ->from('products');
        $q->orderBy(new FuncCallExpr('rand'));
        $this->assertSqlStatements($q, [[new MySQLDriver, 'SELECT name FROM products ORDER BY rand()']]);
    }


    public function testSelectSetOrderBy()
    {
        $q = new SelectQuery;
        $q->select(array('name'))
            ->from('products');
        $q->setOrderBy([ 
            ['name', 'ASC'],
            ['phone', 'DESC'],
        ]);
        $this->assertSqlStatements($q, [[new MySQLDriver, 'SELECT name FROM products ORDER BY name ASC, phone DESC']]);
    }



    public function testSelectWithOrderBy()
    {
        $q = new SelectQuery;
        $q->select(array('name'))
            ->from('products');
        $q->orderBy('name', 'ASC');
        $this->assertSqlStatements($q, [[new MySQLDriver, 'SELECT name FROM products ORDER BY name ASC']]);
    }



    public function testInExprWithQuery() {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;

        $subquery = new SelectQuery;
        $subquery->select(array('product_id'))
            ->from('product_category_junction')
            ->where()
                ->equal('category_id', 2);

        $productQuery = new SelectQuery;
        ok($productQuery);
        $productQuery->select(array('id', 'name'))
            ->from('products', 'p')
            ->where()
                ->in('id', $subquery)
            ;
        $sql = $productQuery->toSql($driver, $args);
        is('SELECT id, name FROM products AS p WHERE id IN (SELECT product_id FROM product_category_junction WHERE category_id = 2)', $sql);
    }

    public function testSelectIndexHint() {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $query = new SelectQuery;
        ok($query);
        $query->select(array('id', 'name', 'phone', 'address'))
            ->from('users', 'u')
            ->indexHintOn('users')->useIndex('idx_users')->forOrderBy();
            ;

        $sql = $query->toSql($driver, $args);
        is('SELECT id, name, phone, address FROM users AS u USE INDEX FOR ORDER BY (idx_users)  USE INDEX FOR ORDER BY (idx_users)', $sql);
    }

    public function testMultipleJoin() {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $query = new SelectQuery;
        ok($query);
        $query->select(array('id', 'name', 'phone', 'address'))
            ->from('users' ,'u')
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
        return $query;
    }

    public function testRightJoin() 
    {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $query = new SelectQuery;
        ok($query);
        $query->select(array('id', 'name', 'phone', 'address'))
            ->from('users' ,'u')
            ;
        $query->rightJoin('posts')
                ->as('p')
                ->on('p.user_id = u.id')
                ;
        $sql = $query->toSql($driver, $args);
        is('SELECT id, name, phone, address FROM users AS u RIGHT JOIN posts AS p ON (p.user_id = u.id)', $sql);
        return $query;
    }

    public function testLeftJoin() {
        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $query = new SelectQuery;
        ok($query);
        $query->select(array('id', 'name', 'phone', 'address'))
            ->from('users' ,'u')
            ;
        $query->leftJoin('posts')
                ->as('p')
                ->on('p.user_id = u.id')
                ;
        $sql = $query->toSql($driver, $args);
        is('SELECT id, name, phone, address FROM users AS u LEFT JOIN posts AS p ON (p.user_id = u.id)', $sql);
        return $query;
    }

    /**
     * @depends testLeftJoin
     */
    public function testMySQLExplain($query) {
        $this->assertSqlStatements(new ExplainQuery($query), [ 
            [new MySQLDriver, 'EXPLAIN SELECT id, name, phone, address FROM users AS u LEFT JOIN posts AS p ON (p.user_id = u.id)']
        ]);
    }



    /**
     * @depends testMultipleJoin
     */
    public function testClone($query)
    {
        $newQuery = clone $query;
        ok($newQuery);

        $args = new ArgumentArray;
        $driver = new MySQLDriver;
        $sql = $query->toSql($driver, $args);
        is('SELECT id, name, phone, address FROM users AS u LEFT JOIN posts AS p ON (p.user_id = u.id) LEFT JOIN ratings AS r ON (r.user_id = u.id)', $sql);
    }


}

