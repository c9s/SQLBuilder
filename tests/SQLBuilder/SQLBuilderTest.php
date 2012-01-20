<?php
namespace SQLBuilder;
use PHPUnit_Framework_TestCase;
use Exception;

class SQLBuilderTest extends PHPUnit_Framework_TestCase
{

	function testInsert()
	{
		$sqlbuilder = new SQLBuilder('Member');
		$sqlbuilder->configure('driver','postgres');
		$sqlbuilder->configure('trim',true);
		$sqlbuilder->configure('placeholder','named');
		$sqlbuilder->insert(array(
			'foo' => 'foo',
			'bar' => 'bar',
		));
		$sql = $sqlbuilder->build();
		is( 'INSERT INTO "Member" ( "foo","bar") VALUES (:foo,:bar)' , $sql );

		$sqlbuilder->configure('placeholder',false);
		$sqlbuilder->insert(array(
			'foo' => 'foo',
			'bar' => 'bar',
		));
		$sql = $sqlbuilder->build();
		is( 'INSERT INTO "Member" ( "foo","bar") VALUES (\'foo\',\'bar\')' , $sql );

		$sqlbuilder->configure('placeholder',true);
		$sql = $sqlbuilder->build();
		is( 'INSERT INTO "Member" ( "foo","bar") VALUES (?,?)' , $sql );
	}

	function testDelete()
	{
		$sqlbuilder = new SQLBuilder('Member');
		$sqlbuilder->configure('driver','postgres');
		$sqlbuilder->configure('trim',true);
		$sqlbuilder->delete();
		$sqlbuilder->where(array( 'foo' => '123' ));

		$sql = $sqlbuilder->build();
		is( 'DELETE FROM "Member"  WHERE "foo" = \'123\'' , $sql );

		$sqlbuilder->configure('placeholder','named');
		$sql = $sqlbuilder->buildDelete();
		is( 'DELETE FROM "Member"  WHERE "foo" = :foo' , $sql );
	}

	function testUpdate()
	{
		$sb = new SQLBuilder('Member');
		$sb->configure('driver','postgres');
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
		$sqlbuilder = new SQLBuilder('Member');
		$sqlbuilder->configure('driver','postgres');
		$sqlbuilder->configure('trim',true);
		$sqlbuilder->select( '*' );

		ok( $sqlbuilder );

		$sql = $sqlbuilder->buildSelect();
		ok( $sql );

		is( 'SELECT * FROM "Member"' , trim($sql));

		$sqlbuilder->configure('placeholder','named');
		$sqlbuilder->where(array(
			'foo' => ':foo',
	   	));


		$sql = $sqlbuilder->buildSelect();
		is( 'SELECT * FROM "Member"  WHERE "foo" = :foo' , $sql );

		$sqlbuilder->select(array('COUNT(*)'));

		$sql = $sqlbuilder->buildSelect();
		is( 'SELECT COUNT(*) FROM "Member"  WHERE "foo" = :foo' , $sql );

		$sqlbuilder->limit(10);

		$sql = $sqlbuilder->buildSelect();
		is( 'SELECT COUNT(*) FROM "Member"  WHERE "foo" = :foo LIMIT 10' ,$sql );

		$sqlbuilder->offset(20);

		$sql = $sqlbuilder->buildSelect();
		is( 'SELECT COUNT(*) FROM "Member"  WHERE "foo" = :foo LIMIT 10 OFFSET 20' ,$sql );
	}
}
