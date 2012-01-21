<?php
namespace SQLBuilder;
use PHPUnit_Framework_TestCase;
use Exception;

class SQLBuilderMySQLTest extends PHPUnit_Framework_TestCase
{

	function testInsert()
	{
		$sb = new SQLBuilder('member');
		$sb->configure('driver','mysql');
		$sb->configure('trim',true);
		$sb->configure('placeholder','named');
		$sb->insert(array(
			'foo' => 'foo',
			'bar' => 'bar',
		));
		$sql = $sb->build();
		is( 'INSERT INTO member ( foo,bar) VALUES (:foo,:bar)' , $sql );

		$sb->configure('placeholder',false);
		$sb->insert(array(
			'foo' => 'foo',
			'bar' => 'bar',
		));
		$sql = $sb->build();
		is( 'INSERT INTO member ( foo,bar) VALUES (\'foo\',\'bar\')' , $sql );

		$sb->configure('placeholder',true);
		$sql = $sb->build();
		is( 'INSERT INTO member ( foo,bar) VALUES (?,?)' , $sql );
	}

	function testDelete()
	{
		$sb = new SQLBuilder('member');
		$sb->configure('driver','mysql');
		$sb->configure('trim',true);
		$sb->delete();
		$sb->where(array( 'foo' => '123' ));

		$sql = $sb->build();
		is( 'DELETE FROM member  WHERE foo = \'123\'' , $sql );

		$sb->configure('placeholder','named');
		$sql = $sb->buildDelete();
		is( 'DELETE FROM member  WHERE foo = :foo' , $sql );
	}

	function testUpdate()
	{
		$sb = new SQLBuilder('member');
		$sb->configure('driver','mysql');
		$sb->configure('trim',true);
		$sb->configure('placeholder','named');
		$sb->where(array( 
			'cond1' => ':blah',
		));
		$sb->update( array( 'set1' => 'value1') );
		$sql = $sb->buildUpdate();
		is( 'UPDATE member SET set1 = :set1 WHERE cond1 = :cond1' , $sql );

		$sb->configure('placeholder',false);
		$sql = $sb->buildUpdate();
        is( 'UPDATE member SET set1 = \'value1\' WHERE cond1 = \':blah\'' , $sql );
	}

	function testSelect()
	{
		$sb = new SQLBuilder('member');
		$sb->configure('driver','mysql');
		$sb->configure('trim',true);
		$sb->select( '*' );

		ok( $sb );

		$sql = $sb->buildSelect();
		ok( $sql );

		is( 'SELECT * FROM member' , trim($sql));

		$sb->configure('placeholder','named');
		$sb->where(array(
			'foo' => ':foo',
	   	));


		$sql = $sb->buildSelect();
		is( 'SELECT * FROM member  WHERE foo = :foo' , $sql );

		$sb->select(array('COUNT(*)'));

		$sql = $sb->buildSelect();
		is( 'SELECT COUNT(*) FROM member  WHERE foo = :foo' , $sql );

		$sb->limit(10);

		$sql = $sb->buildSelect();
		is( 'SELECT COUNT(*) FROM member  WHERE foo = :foo LIMIT 10' ,$sql );

		$sb->offset(20);

		$sql = $sb->buildSelect();
		is( 'SELECT COUNT(*) FROM member  WHERE foo = :foo LIMIT 20 , 10' ,$sql );
	}


}
