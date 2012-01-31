<?php

namespace SQLBuilder;
use PHPUnit_Framework_TestCase;
use Exception;

class SQLBuilderTest extends PHPUnit_Framework_TestCase
{

    public function getPgDriver()
    {
        $driver = new Driver;
        $driver->configure('driver','postgresql');
        $driver->configure('quote_table',true);
        $driver->configure('quote_column',true);
        $driver->configure('trim',true);
        $driver->configure('placeholder','named');
        return $driver;
    }

    function testInsert()
    {
        $driver = $this->getPgDriver();

        $sb = new CRUDBuilder;
        $sb->driver = $driver;

        $sb->table('Member')->insert(array(
            'foo' => 'foo',
            'bar' => 'bar',
        ));
        $sql = $sb->build();
        is( 'INSERT INTO "Member" ( "foo","bar") VALUES (:foo,:bar)' , $sql );

        $driver->configure('placeholder',false);
        $sb->insert(array(
            'foo' => 'foo',
            'bar' => 'bar',
        ));
        $sql = $sb->build();
        is( 'INSERT INTO "Member" ( "foo","bar") VALUES (\'foo\',\'bar\')' , $sql );

        $driver->configure('placeholder',true);
        $sql = $sb->build();
        is( 'INSERT INTO "Member" ( "foo","bar") VALUES (?,?)' , $sql );
    }

    function testDelete()
    {
        $driver = new Driver;
        $driver->configure('driver','postgresql');
        $driver->configure('trim',true);
        $driver->configure('quote_table',true);
        $driver->configure('quote_column',true);

        $sb = new CRUDBuilder;
        $sb->driver = $driver;
        $sb->table('Member')->delete();
        $sb->whereFromArgs(array( 'foo' => '123' ));

        $sql = $sb->build();
        is( 'DELETE FROM "Member"  WHERE "foo" = \'123\'' , $sql );

        $driver->configure('placeholder','named');
        $sql = $sb->buildDelete();
        is( 'DELETE FROM "Member"  WHERE "foo" = :foo' , $sql );
    }

    function testUpdate()
    {
        $d = new Driver;
        $d->configure('driver','postgresql');
        $d->configure('quote_table',true);
        $d->configure('quote_column',true);
        $d->configure('trim',true);
        $d->configure('placeholder','named');

        $sb = new CRUDBuilder;
        $sb->table('Member');
        $sb->driver = $d;
        $sb->whereFromArgs(array( 
            'cond1' => ':blah',
        ));
        $sb->update( array( 'set1' => 'value1') );
        $sql = $sb->buildUpdate();
        is( 'UPDATE "Member" SET "set1" = :set1 WHERE "cond1" = :cond1' , $sql );

        $d->configure('placeholder',false);
        $sql = $sb->buildUpdate();
        is( 'UPDATE "Member" SET "set1" = \'value1\' WHERE "cond1" = \':blah\'' , $sql );
    }

    function testSelect()
    {
        $d = new Driver;
        $d->configure('driver','postgresql');
        $d->configure('quote_table',true);
        $d->configure('quote_column',true);
        $d->configure('trim',true);

        $sb = new CRUDBuilder();
        $sb->driver = $d;
        $sb->table('Member');
        $sb->select( '*' );

        ok( $sb );

        $sql = $sb->build();
        ok( $sql );

        is( 'SELECT * FROM "Member"' , trim($sql));

        $d->configure('placeholder','named');
        $sb->whereFromArgs(array(
            'foo' => ':foo',
        ));

        $sql = $sb->build();
        is( 'SELECT * FROM "Member"  WHERE "foo" = :foo' , $sql );

        $sb->select(array('COUNT(*)'));

        $sql = $sb->build();
        is( 'SELECT COUNT(*) FROM "Member"  WHERE "foo" = :foo' , $sql );

        $sb->limit(10);

        $sql = $sb->build();
        is( 'SELECT COUNT(*) FROM "Member"  WHERE "foo" = :foo LIMIT 10' ,$sql );

        $sb->offset(20);

        $sql = $sb->build();
        is( 'SELECT COUNT(*) FROM "Member"  WHERE "foo" = :foo LIMIT 10 OFFSET 20' ,$sql );

    }
}
