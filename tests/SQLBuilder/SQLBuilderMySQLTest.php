<?php
use SQLBuilder\QueryBuilder;
use SQLBuilder\Driver\MySQLDriver;

class SQLQueryBuilderMySQLTest extends PHPUnit_PDO_TestCase
{

    public $envVariablePrefix = 'MYSQL_';

    public $schema = array( 'tests/schema/member_mysql.sql' );

    public function getDriver()
    {
        $d = new MySQLDriver;
        $d->setTrim(true);
        $d->setNamedParamMarker();
        return $d;
    }

    public function testInsert()
    {
        $sb = new QueryBuilder($this->getDriver());
        $sb->table('member');

        $sql = $sb->insert(array(
            'foo' => 'foo',
            'bar' => 'bar',
        ))->build();
        is( 'INSERT INTO member (foo,bar) VALUES (:foo,:bar)' , $sql );

        $sb->driver->setNoParamMarker();
        $sql = $sb->insert(array(
            'foo' => 'foo',
            'bar' => 'bar',
            ))->build();
        is( 'INSERT INTO member (foo,bar) VALUES (\'foo\',\'bar\')' , $sql );


        $sb->driver->setQMarkParamMarker();
        $sql = $sb->build();
        is( 'INSERT INTO member (foo,bar) VALUES (?,?)' , $sql );
    }

    function testSelect()
    {
        $driver = new MySQLDriver;
        $driver->setTrim(true);

        $sb = new QueryBuilder($driver);
        $sb->table('member');
        $sb->select( '*' );

        ok( $sb );

        $sql = $sb->build();
        ok( $sql );

        is( 'SELECT * FROM member' , trim($sql));

        $driver->setNamedParamMarker();
        $sb->whereFromArgs(array(
            'foo' => ':foo',
        ));


        $sql = $sb->build();
        is( 'SELECT * FROM member WHERE foo = :foo' , $sql);

        $sb->select(array('COUNT(*)')); // override current query

        $sql = $sb->build();
        is( 'SELECT COUNT(*) FROM member WHERE foo = :foo' , $sql );

        $sb->limit(10);

        $sql = $sb->build();
        is( 'SELECT COUNT(*) FROM member WHERE foo = :foo LIMIT 10' ,$sql );

        $sb->offset(20);

        $sql = $sb->build();
        is( 'SELECT COUNT(*) FROM member WHERE foo = :foo LIMIT 20 , 10' ,$sql );
    }

    public function testDelete()
    {
        $driver = $this->getDriver();
        $driver->setTrim(true);
        $driver->setNoParamMarker();

        $sb = new QueryBuilder($driver);
        $sb->table('member');
        $sb->delete();
        $sb->whereFromArgs(array( 'foo' => 'string' ));
        $sql = $sb->build();

        is( 'DELETE FROM member WHERE foo = \'string\'' , $sql );

        $driver->setNamedParamMarker();
        $sql = $sb->build();
        is( 'DELETE FROM member WHERE foo = :foo' , $sql );
    }

    public function testUpdate()
    {
        $driver = new MySQLDriver;
        $driver->setTrim(true);
        $driver->setNamedParamMarker(true);

        $sb = new QueryBuilder($driver);
        $sb->table('member');
        $sb->whereFromArgs(array( 
            'cond1' => ':blah',
        ));
        $sb->update( array( 'set1' => 'value1') );
        $sql = $sb->build();
        is('UPDATE member SET set1 = :set1 WHERE cond1 = :cond1' , $sql );

        $driver->setNoParamMarker();
        $sql = $sb->build();
        is('UPDATE member SET set1 = \'value1\' WHERE cond1 = \':blah\'' , $sql );
    }

    public function testSelectWithJoin()
    {
        $driver = new MySQLDriver;
        $driver->setTrim(true);

        $sb = new QueryBuilder($driver);
        $sb->table('member');
        $sb->select( '*' )
            ->alias('m');

        $back = $sb->join('tweets')
            ->alias('t')
            ->on()->equal('t.member_id',array('m.id'))->back();

        ok($back);
        is($back,$sb);

        $sb->where()
            ->equal('m.name', 'John')
            ->and()
            ->equal('m.phone', '12345678')
            ;

        $sql = $back->build();
        is("SELECT * FROM member m LEFT JOIN tweets t ON (t.member_id = m.id) WHERE m.name = :m_name AND m.phone = :m_phone", $sql );
    }

    public function testCascading()
    {
        $driver = new MySQLDriver;

        $sb = new QueryBuilder($driver);
        $sql = $sb->table('member')
            ->select( '*' )
            ->where()
                ->equal('id',1)
                ->equal('name','foo')
                ->back()
            ->join('tweets')->alias('t')->on()->equal('t',array('m.id'))
                ->back()
            ->build();
        ok($sql);
    }

    public function testRawSqlForUpdate()
    {
        $driver = new MySQLDriver;
        $driver->setNamedParamMarker(true);

        $sb = new QueryBuilder($driver);
        $sb->table('member');
        $sb->update(array( 
            'created_on' => array('current_timestamp'),
        ));
        $sql = $sb->build();
        is( 'UPDATE member SET created_on = current_timestamp', $sql );
    }

    public function testRawSqlForInsert()
    {
        $driver = new MySQLDriver;
        $driver->setNamedParamMarker();

        $sb = new QueryBuilder($driver);
        $sb->table('member');
        $sb->insert(array( 
            'created_on' => array('current_timestamp'),
        ));
        $sql = $sb->build();
        is( 'INSERT INTO member (created_on) VALUES (current_timestamp)', $sql );
    }
}
