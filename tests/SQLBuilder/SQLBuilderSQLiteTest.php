<?php

class TestQueryWrapper extends SQLBuilder\QueryBuilder
{

    /**
     * To archive this syntax:
     *
     *    $obj->delete()
     *          ->where()
     *              ->equal('id',1)
     *              ->back()
     *          ->execute();
     */

    public function execute()
    {
        return true;
    }
}

class SQLBuilderSQLiteTest extends PHPUnit_Framework_TestCase
{

    function getDriver()
    {
        $d = new SQLBuilder\Driver;
        $d->configure('driver','sqlite');
        return $d;
    }

    function testWrapper()
    {
        $test = new TestQueryWrapper;
        $ret = $test->delete()
                ->where()
                    ->equal('id',1)
                ->back()->execute();
        is( true, $ret );
    }

    function testInsert()
    {
        $pdo = new PDO('sqlite::memory:');

        $sb = new SQLBuilder\QueryBuilder;
        $sb->table('member');
        $sb->driver = $this->getDriver();
        $sb->insert(array(
            'foo' => 'foo',
            'bar' => 'bar',
        ));
        $sql = $sb->build();


    }

    function testGroupBy()
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->query( 'create table member ( name varchar(128) , phone varchar(128) , country varchar(128) );' );
        ok( $pdo );

        $stm = $pdo->prepare('insert into member ( name, phone, country ) values ( :name, :phone, :country ) ');
        $countries = array('Taiwan','Japan','China','Taipei');

        foreach( $countries as $country ) {
            foreach( range(1,20) as $i ) {
                $stm->execute(array( $i , $i , $country ));
            }
        }

        $sb = new SQLBuilder\QueryBuilder;
        $sb->driver = $this->getDriver();
        $sb->table('member')->select('name')
            ->groupBy('country')
            ->order('name');
        $sql = $sb->build();
        $stm = $pdo->query( $sql );

        $err = $pdo->errorInfo();
        ok( $err[1] == null );

        $row = $stm->fetch();
        $row2 = $stm->fetch();
        ok( $row );
        ok( $row2 );
    }
}

