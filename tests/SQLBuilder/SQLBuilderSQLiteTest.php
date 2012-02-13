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
    public $pdo;

    function setup()
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->query( 'create table member ( name varchar(128) , phone varchar(128) , country varchar(128) );' );
        $this->pdo->setAttribute( PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
    }

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
        $sb = new SQLBuilder\QueryBuilder;
        $sb->table('member');
        $sb->driver = $this->getDriver();
        $sb->insert(array(
            'name' => 'foo',
            'phone' => 'bar',
        ));
        $sql = $sb->build();
        ok( $sql );
        is("INSERT INTO member ( name,phone) VALUES ('foo','bar')",$sql);
    }

    function testQuoteInsert() 
    {
        $sb = new SQLBuilder\QueryBuilder;
        $sb->table('member');
        $sb->driver = $this->getDriver();
        $sb->driver->configure('quote_column',true);
        $sb->driver->quoter = array( $this->pdo, 'quote' );
        $sb->insert(array(
            'name' => 'foo',
            'phone' => 'bar',
        ));
        $sql = $sb->build();
        ok( $sql );
        is("INSERT INTO member ( `name`,`phone`) VALUES ('foo','bar')",$sql);
        $stm = $this->pdo->query($sql);
        ok( $stm );
    }

    function testQuoteInsert2()
    {
        $sb = new SQLBuilder\QueryBuilder;
        $sb->table('member');
        $sb->driver = $this->getDriver();
        $sb->driver->configure('quote_column',true);
        $sb->driver->quoter = array( $this->pdo, 'quote' );
        $sb->insert(array(
            'name' => 'fo\'o',
            'phone' => 'bar',
        ));
        $sql = $sb->build();
        ok( $sql );
        is("INSERT INTO member ( `name`,`phone`) VALUES ('fo''o','bar')",$sql);
        $stm = $this->pdo->query($sql);
        ok( $stm );
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

        $sb->driver->quoter = array($pdo,'quote');

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

