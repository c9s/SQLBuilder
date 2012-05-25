<?php
use SQLBuilder\QueryBuilder;
use SQLBuilder\Driver;

// class SQLBuilderPgTest extends PHPUnit_Framework_TestCase
class SQLBuilderPgTest extends PHPUnit_PDO_TestCase
{
    public $dsn = 'pgsql:dbname=sqlbuilder_test';

    function schema()
    {
        $sqls = array();

        $sqls[] =<<<EOS
DROP SEQUENCE IF EXISTS "memberno_seq"
EOS;

        $sqls[] =<<<EOS
CREATE SEQUENCE "memberno_seq"
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
EOS;

        $sqls[] =<<<EOS
DROP TABLE IF EXISTS "Member";
EOS;

        $sqls[] =<<<EOS
CREATE TABLE "Member" (
    "MemberNo" integer primary key default nextval('memberno_seq'),
    "MemberName" varchar(128),
    "MemberConfirm" boolean
);
EOS;


        return $sqls;
    }

    public function tearDown()
    {
        $this->pdo->query('drop table "Member" ');
        $this->pdo->query('drop sequence "memberno_seq" ');
    }

    function getPgDriver()
    {
        $driver = new SQLBuilder\Driver;
        $driver->configure('driver','pgsql');
        $driver->configure('quote_table',true);
        $driver->configure('quote_column',true);
        $driver->configure('trim',true);
        $driver->configure('placeholder','named');
        return $driver;
    }

    function testInsert()
    {
        $driver = $this->getPgDriver();

        $sb = new QueryBuilder;
        $sb->driver = $driver;
        $sb->table('Member')->insert(array(
            'MemberName' => 'insert',
            'MemberConfirm' => true,
        ));
        $sql = $sb->build();
        is( 'INSERT INTO "Member" ( "MemberName","MemberConfirm") VALUES (:MemberName,:MemberConfirm)' , $sql );

        $this->executeOk( $sql , array( 
            ':MemberName' => 'insert',
            ':MemberConfirm' => true
        ));

        $record = $this->recordOk( 'select * from "Member" where "MemberName" = \'insert\' ' );
        ok( $record['MemberConfirm'] );



        $driver->configure('placeholder',false);
        $sb->insert(array(
            'MemberName' => 'insert2',
            'MemberConfirm' => true,
        ));
        $sql = $sb->build();
        is( 'INSERT INTO "Member" ( "MemberName","MemberConfirm") VALUES (\'insert2\',TRUE)' , $sql );
        $this->queryOk( $sql );

        $record = $this->recordOk( 'select * from "Member" where "MemberName" = \'insert2\' ' );
        ok( $record['MemberConfirm'] );



        $driver->configure('placeholder',true);
        $sql = $sb->build();
        is( 'INSERT INTO "Member" ( "MemberName","MemberConfirm") VALUES (?,?)' , $sql );

        $this->executeOk( $sql , array( 'insert3' , 1 ) );

        $record = $this->recordOk( 'select * from "Member" where "MemberName" = \'insert3\' ' );
        ok( $record['MemberConfirm'] );
    }

    function testDelete()
    {
        $driver = $this->getPgDriver();
        $driver->configure('placeholder',null); // inflate values

        $sb = new QueryBuilder;
        $sb->driver = $driver;
        $sb->table('Member')->delete();
        $sb->whereFromArgs(array( 'foo' => '123' ));

        $sql = $sb->build();
        is( 'DELETE FROM "Member"  WHERE "foo" = \'123\'' , $sql );

        $driver->configure('placeholder','named');
        $sql = $sb->build();
        is( 'DELETE FROM "Member"  WHERE "foo" = :foo' , $sql );
    }

    function testUpdate()
    {
        $d = new Driver;
        $d->configure('driver','pgsql');
        $d->configure('quote_table',true);
        $d->configure('quote_column',true);
        $d->configure('trim',true);
        $d->configure('placeholder','named');

        $sb = new QueryBuilder;
        $sb->table('Member');
        $sb->driver = $d;
        $sb->whereFromArgs(array( 
            'cond1' => ':blah',
        ));
        $sb->update( array( 'set1' => 'value1') );
        $sql = $sb->build();
        is( 'UPDATE "Member" SET "set1" = :set1 WHERE "cond1" = :cond1' , $sql );

        $d->configure('placeholder',false);
        $sql = $sb->build();
        is( 'UPDATE "Member" SET "set1" = \'value1\' WHERE "cond1" = \':blah\'' , $sql );
    }

    function testSelect()
    {
        $d = new Driver;
        $d->configure('driver','pgsql');
        $d->configure('quote_table',true);
        $d->configure('quote_column',true);
        $d->configure('trim',true);

        $sb = new QueryBuilder();
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
