<?php
use SQLBuilder\QueryBuilder;
use SQLBuilder\Driver\PgSQLDriver;

// class SQLBuilderPgTest extends PHPUnit_Framework_TestCase
class SQLBuilderPgTest extends PHPUnit_PDO_TestCase
{
    public $envVariablePrefix = 'PGSQL_';

    public function schema()
    {
        $sqls = array();

        $sqls[] =<<<EOS
DROP SEQUENCE IF EXISTS "memberno_seq" CASCADE
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

    public function getPgDriver()
    {
        $driver = new PgSQLDriver;
        $driver->setQuoteTable(true);
        $driver->setQuoteColumn(true);
        $driver->setTrim(true);
        $driver->setNamedParamMarker();
        return $driver;
    }

    public function testInsert()
    {
        $driver = $this->getPgDriver();

        $sb = new QueryBuilder($driver);
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


        $driver->setNoParamMarker();
        $sb->insert(array(
            'MemberName' => 'insert2',
            'MemberConfirm' => true,
        ));
        $sql = $sb->build();
        is( 'INSERT INTO "Member" ( "MemberName","MemberConfirm") VALUES (\'insert2\',TRUE)' , $sql );
        $this->queryOk( $sql );

        $record = $this->recordOk( 'select * from "Member" where "MemberName" = \'insert2\' ' );
        ok( $record['MemberConfirm'] );


        $driver->setQMarkParamMarker();
        $sql = $sb->build();
        is( 'INSERT INTO "Member" ( "MemberName","MemberConfirm") VALUES (?,?)' , $sql );

        $this->executeOk( $sql , array( 'insert3' , 1 ) );

        $record = $this->recordOk( 'select * from "Member" where "MemberName" = \'insert3\' ' );
        ok( $record['MemberConfirm'] );
    }

    public function testDelete()
    {
        $driver = $this->getPgDriver();
        $driver->setNoParamMarker();

        $sb = new QueryBuilder($driver);
        $sb->table('Member')->delete();
        $sb->whereFromArgs(array( 'foo' => '123' ));

        $sql = $sb->build();
        is( 'DELETE FROM "Member"  WHERE "foo" = \'123\'' , $sql );

        $driver->setNamedParamMarker();
        $sql = $sb->build();
        is( 'DELETE FROM "Member"  WHERE "foo" = :foo' , $sql );
    }

    public function testUpdate()
    {
        $d = new PgSQLDriver;
        $d->setQuoteTable(true);
        $d->setQuoteColumn(true);
        $d->setTrim();
        $d->setNamedParamMarker();

        $sb = new QueryBuilder($d);
        $sb->table('Member');
        $sb->whereFromArgs(array( 
            'cond1' => ':blah',
        ));
        $sb->update( array( 'set1' => 'value1') );
        $sql = $sb->build();
        is( 'UPDATE "Member" SET "set1" = :set1 WHERE "cond1" = :cond1' , $sql );

        $d->setNoParamMarker();
        $sql = $sb->build();
        is( 'UPDATE "Member" SET "set1" = \'value1\' WHERE "cond1" = \':blah\'' , $sql );
    }

    public function testSelect()
    {
        $d = new PgSQLDriver;
        $d->setQuoteTable(true);
        $d->setQuoteColumn(true);
        $d->setTrim(true);

        $sb = new QueryBuilder($d);
        $sb->table('Member');
        $sb->select('*');

        ok( $sb );

        $sql = $sb->build();
        ok( $sql );

        is( 'SELECT * FROM "Member"' , trim($sql));

        $d->setNamedParamMarker();
        $sb->whereFromArgs(array(
            'foo' => ':foo',
        ));

        $sql = $sb->build();
        is( 'SELECT * FROM "Member"  WHERE "foo" = :foo' , $sql );

        $sb->select(array('COUNT(*)'));  // override current select query

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
