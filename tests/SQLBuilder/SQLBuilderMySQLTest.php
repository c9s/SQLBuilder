<?php

use SQLBuilder\QueryBuilder;
use SQLBuilder\Driver;

class QueryBuilderMySQLTest extends PHPUnit_PDO_TestCase
{
    public $schema = array(
        'member.sql',
    );

    function getDriver()
    {
        $d = new Driver;
        $d->configure('driver','mysql');
        $d->configure('trim',true);
        $d->configure('placeholder','named');
        return $d;
    }


    function testInsert()
    {
        $sb = new QueryBuilder;
        $sb->table('member');
        $sb->driver = $this->getDriver();

        $sql = $sb->insert(array(
            'foo' => 'foo',
            'bar' => 'bar',
        ))->build();
        is( 'INSERT INTO member ( foo,bar) VALUES (:foo,:bar)' , $sql );

        $sb->driver->configure('placeholder',false);
        $sql = $sb->insert(array(
            'foo' => 'foo',
            'bar' => 'bar',
            ))->build();
        is( 'INSERT INTO member ( foo,bar) VALUES (\'foo\',\'bar\')' , $sql );

        $sb->driver->configure('placeholder',true);
        $sql = $sb->build();
        is( 'INSERT INTO member ( foo,bar) VALUES (?,?)' , $sql );
    }

    function testDelete()
    {
        $sb = new QueryBuilder;
        $sb->table('member');
        $sb->driver = new Driver;
        $sb->driver->configure('driver','mysql');
        $sb->driver->configure('trim',true);
        $sb->delete();
        $sb->whereFromArgs(array( 'foo' => 'string' ));
        $sql = $sb->build();
        is( 'DELETE FROM member  WHERE foo = \'string\'' , $sql );

        $sb->driver->configure('placeholder','named');
        $sql = $sb->build();
        is( 'DELETE FROM member  WHERE foo = :foo' , $sql );
    }

    function testUpdate()
    {
        $sb = new QueryBuilder;
        $sb->table('member');
        $sb->driver = new Driver;
        $sb->driver->configure('driver','mysql');
        $sb->driver->configure('trim',true);
        $sb->driver->configure('placeholder','named');
        $sb->whereFromArgs(array( 
            'cond1' => ':blah',
        ));
        $sb->update( array( 'set1' => 'value1') );
        $sql = $sb->build();
        is( 'UPDATE member SET set1 = :set1 WHERE cond1 = :cond1' , $sql );

        $sb->driver->configure('placeholder',false);
        $sql = $sb->build();
        is( 'UPDATE member SET set1 = \'value1\' WHERE cond1 = \':blah\'' , $sql );
    }

    function testSelectWithJoin()
    {
        $sb = new QueryBuilder;
        $sb->table('member');
        $sb->driver = new Driver;
        $sb->driver->configure('driver','mysql');
        $sb->driver->configure('trim',true);
        $sb->select( '*' )
            ->alias('m');
        $back = $sb->join('tweets')
            ->alias('t')
            ->on()->equal('t.member_id',array('m.id'))->back();

        ok($back);
        is($back,$sb);

        $sql = $back->build();
        is("SELECT * FROM member m  LEFT JOIN tweets t ON (t.member_id = m.id)", $sql );
    }

    function testCascading()
    {
        $sb = new QueryBuilder;
        $sb->driver = new Driver;
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

    function testRawSqlForUpdate()
    {
        $sb = new QueryBuilder;
        $sb->table('member');
        $sb->driver = new Driver;
        $sb->driver->configure('driver','mysql');
        $sb->driver->configure('placeholder','named');
        $sb->update(array( 
            'created_on' => array('current_timestamp'),
        ));
        $sql = $sb->build();
        is( 'UPDATE member SET created_on = current_timestamp', $sql );
    }

    function testRawSqlForInsert()
    {
        $sb = new QueryBuilder;
        $sb->table('member');
        $sb->driver = new Driver;
        $sb->driver->configure('driver','mysql');
        $sb->driver->configure('placeholder','named');
        $sb->insert(array( 
            'created_on' => array('current_timestamp'),
        ));
        $sql = $sb->build();
        is( 'INSERT INTO member ( created_on) VALUES (current_timestamp)', $sql );
    }

    function testSelect()
    {
        $sb = new QueryBuilder;
        $sb->table('member');
        $sb->driver = new Driver;
        $sb->driver->configure('driver','mysql');
        $sb->driver->configure('trim',true);
        $sb->select( '*' );

        ok( $sb );

        $sql = $sb->build();
        ok( $sql );

        is( 'SELECT * FROM member' , trim($sql));

        $sb->driver->configure('placeholder','named');
        $sb->whereFromArgs(array(
            'foo' => ':foo',
        ));


        $sql = $sb->build();
        is( 'SELECT * FROM member  WHERE foo = :foo' , $sql );

        $sb->select(array('COUNT(*)')); // override current query

        $sql = $sb->build();
        is( 'SELECT COUNT(*) FROM member  WHERE foo = :foo' , $sql );

        $sb->limit(10);

        $sql = $sb->build();
        is( 'SELECT COUNT(*) FROM member  WHERE foo = :foo LIMIT 10' ,$sql );

        $sb->offset(20);

        $sql = $sb->build();
        is( 'SELECT COUNT(*) FROM member  WHERE foo = :foo LIMIT 20 , 10' ,$sql );
    }


}
