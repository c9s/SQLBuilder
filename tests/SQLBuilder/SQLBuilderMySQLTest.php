<?php
namespace SQLBuilder;
use PHPUnit_Framework_TestCase;
use Exception;

class CRUDBuilderMySQLTest extends PHPUnit_Framework_TestCase
{

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
        $sb = new CRUDBuilder('member');
        $sb->driver = $this->getDriver();

        $sb->insert(array(
            'foo' => 'foo',
            'bar' => 'bar',
        ));
        $sql = $sb->build();
        is( 'INSERT INTO member ( foo,bar) VALUES (:foo,:bar)' , $sql );

        $sb->driver->configure('placeholder',false);
        $sb->insert(array(
            'foo' => 'foo',
            'bar' => 'bar',
        ));
        $sql = $sb->build();
        is( 'INSERT INTO member ( foo,bar) VALUES (\'foo\',\'bar\')' , $sql );

        $sb->driver->configure('placeholder',true);
        $sql = $sb->build();
        is( 'INSERT INTO member ( foo,bar) VALUES (?,?)' , $sql );
    }

    function testDelete()
    {
        $sb = new CRUDBuilder('member');
        $sb->driver = new Driver;
        $sb->driver->configure('driver','mysql');
        $sb->driver->configure('trim',true);
        $sb->delete();
        $sb->whereFromArgs(array( 'foo' => '123' ));

        $sql = $sb->build();
        is( 'DELETE FROM member  WHERE foo = \'123\'' , $sql );

        $sb->driver->configure('placeholder','named');
        $sql = $sb->buildDelete();
        is( 'DELETE FROM member  WHERE foo = :foo' , $sql );
    }

    function testUpdate()
    {
        $sb = new CRUDBuilder('member');
        $sb->driver = new Driver;
        $sb->driver->configure('driver','mysql');
        $sb->driver->configure('trim',true);
        $sb->driver->configure('placeholder','named');
        $sb->whereFromArgs(array( 
            'cond1' => ':blah',
        ));
        $sb->update( array( 'set1' => 'value1') );
        $sql = $sb->buildUpdate();
        is( 'UPDATE member SET set1 = :set1 WHERE cond1 = :cond1' , $sql );

        $sb->driver->configure('placeholder',false);
        $sql = $sb->buildUpdate();
        is( 'UPDATE member SET set1 = \'value1\' WHERE cond1 = \':blah\'' , $sql );
    }

    function testSelectWithJoin()
    {
        $sb = new CRUDBuilder('member');
        $sb->driver = new Driver;
        $sb->driver->configure('driver','mysql');
        $sb->driver->configure('trim',true);
        $sb->select( '*' );

        $sb->alias('m');
        $back = $sb->join('tweets')
            ->alias('t')
            ->on()->equal('t.member_id',array('m.id'))->back();

        ok($back);
        is($back,$sb);

        $sql = $sb->build();
        is("SELECT * FROM member m  LEFT JOIN tweets t ON (t.member_id = m.id)", $sql );
    }

    function testSelect()
    {
        $sb = new CRUDBuilder('member');
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

        $sb->select(array('COUNT(*)'));

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
