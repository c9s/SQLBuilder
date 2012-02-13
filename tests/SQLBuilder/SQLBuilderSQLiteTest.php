<?php

class TestQueryWrapper extends SQLBuilder\QueryBuilder
{

    /**
     * To archive this syntax:
     *
     *    $obj->delete()
     *          ->where()
     *              ->equal('id',1)
     *          ->execute();
     */

    public function execute()
    {
        return 99;
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
                ->execute();
        is( 99, $ret );
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
        $stm = $this->pdo->prepare('insert into member ( name, phone, country ) values ( :name, :phone, :country ) ');
        $countries = array('Taiwan','Japan','China','Taipei');
        foreach( $countries as $country ) {
            foreach( range(1,20) as $i ) {
                $stm->execute(array( $i , $i , $country ));
            }
        }

        $sb = new SQLBuilder\QueryBuilder;
        $sb->driver = $this->getDriver();
        $sb->driver->quoter = array($this->pdo,'quote');
        $sb->table('member')->select('name')
            ->groupBy('country','name')
            ->order('name');
        $sql = $sb->build();

        is('SELECT name FROM member  GROUP BY country,name ORDER BY name desc', $sql );
        
        $stm = $this->pdo->query( $sql );

        $row = $stm->fetch();
        $row2 = $stm->fetch();
        ok( $row );
        ok( $row2 );

        $sb->having()->equal('name','Taiwan');
        $sql = $sb->build();

        is( "SELECT name FROM member  GROUP BY country,name HAVING name = 'Taiwan' ORDER BY name desc", $sql );
        $this->pdo->query( $sql );

        $sb->table('member')->select('name')
            ->where()
                ->equal('name','ZZ')
            ->groupBy('country','name')
            ->order('name');
        $sql = $sb->build();
        ok( $sql );
    }
}

