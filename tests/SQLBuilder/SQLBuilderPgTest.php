<?php

namespace SQLBuilder;
use PHPUnit_Framework_TestCase;
use Exception;

class SQLBuilderTest extends PHPUnit_Framework_TestCase
{

	function testInsert()
	{
		$sb = new CRUDBuilder('Member');
		$sb->configure('driver','postgresql');
		$sb->configure('quote_table',true);
		$sb->configure('quote_column',true);
		$sb->configure('trim',true);
		$sb->configure('placeholder','named');
		$sb->insert(array(
			'foo' => 'foo',
			'bar' => 'bar',
		));
		$sql = $sb->build();
		is( 'INSERT INTO "Member" ( "foo","bar") VALUES (:foo,:bar)' , $sql );

		$sb->configure('placeholder',false);
		$sb->insert(array(
			'foo' => 'foo',
			'bar' => 'bar',
		));
		$sql = $sb->build();
		is( 'INSERT INTO "Member" ( "foo","bar") VALUES (\'foo\',\'bar\')' , $sql );

		$sb->configure('placeholder',true);
		$sql = $sb->build();
		is( 'INSERT INTO "Member" ( "foo","bar") VALUES (?,?)' , $sql );
	}

	function testDelete()
	{
		$sb = new CRUDBuilder('Member');
		$sb->configure('driver','postgresql');
		$sb->configure('trim',true);
		$sb->configure('quote_table',true);
		$sb->configure('quote_column',true);
		$sb->delete();
		$sb->where(array( 'foo' => '123' ));

		$sql = $sb->build();
		is( 'DELETE FROM "Member"  WHERE "foo" = \'123\'' , $sql );

		$sb->configure('placeholder','named');
		$sql = $sb->buildDelete();
		is( 'DELETE FROM "Member"  WHERE "foo" = :foo' , $sql );
	}

	function testUpdate()
	{
		$sb = new CRUDBuilder('Member');
		$sb->configure('driver','postgresql');
		$sb->configure('quote_table',true);
		$sb->configure('quote_column',true);
		$sb->configure('trim',true);
		$sb->configure('placeholder','named');
		$sb->where(array( 
			'cond1' => ':blah',
		));
		$sb->update( array( 'set1' => 'value1') );
		$sql = $sb->buildUpdate();
		is( 'UPDATE "Member" SET "set1" = :set1 WHERE "cond1" = :cond1' , $sql );

		$sb->configure('placeholder',false);
		$sql = $sb->buildUpdate();
        is( 'UPDATE "Member" SET "set1" = \'value1\' WHERE "cond1" = \':blah\'' , $sql );
	}

	function testSelect()
	{
		$sb = new CRUDBuilder('Member');
		$sb->configure('driver','postgresql');
		$sb->configure('quote_table',true);
		$sb->configure('quote_column',true);
		$sb->configure('trim',true);
		$sb->select( '*' );

		ok( $sb );

		$sql = $sb->buildSelect();
		ok( $sql );

		is( 'SELECT * FROM "Member"' , trim($sql));

		$sb->configure('placeholder','named');
		$sb->where(array(
			'foo' => ':foo',
	   	));


		$sql = $sb->buildSelect();
		is( 'SELECT * FROM "Member"  WHERE "foo" = :foo' , $sql );

		$sb->select(array('COUNT(*)'));

		$sql = $sb->buildSelect();
		is( 'SELECT COUNT(*) FROM "Member"  WHERE "foo" = :foo' , $sql );

		$sb->limit(10);

		$sql = $sb->buildSelect();
		is( 'SELECT COUNT(*) FROM "Member"  WHERE "foo" = :foo LIMIT 10' ,$sql );

		$sb->offset(20);

		$sql = $sb->buildSelect();
		is( 'SELECT COUNT(*) FROM "Member"  WHERE "foo" = :foo LIMIT 10 OFFSET 20' ,$sql );
	}
}
