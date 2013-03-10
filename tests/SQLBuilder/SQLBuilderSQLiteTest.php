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

class SQLBuilderSQLiteTest extends PHPUnit_PDO_TestCase
{
    public $schema = array( 'tests/schema/member.sql' );

    public $dsn = 'sqlite::memory:';

    function getDriver()
    {
        return DriverFactory::create_sqlite_driver();
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

    function testCloneWithNamedParameter()
    {
        $b1 = new SQLBuilder\QueryBuilder;
        $b1->driver = $this->getDriver();
        $b1->driver->configure('placeholder','named');
        $b1->table('member');
        $b1->where()
                ->equal('name','Cindy');
        $b1->build();

        is(array( ':name' => 'Cindy' ), $b1->vars);

        $b2 = clone $b1;

        ok($b2->where);
        is( array( 'name' , '=' , 'Cindy' ), $b2->where->op );
        is( array( 'name' , '=' , 'Cindy' ), $b1->where->op );
        
        ok($b2->where !== $b1->where );
        is($b2->where->op , $b1->where->op ); // the same operand (copy array)
        is($b2->vars, $b1->vars );
        is(array( ':name' => 'Cindy' ), $b2->vars);


        $b2->build();
        is(array( ':name' => 'Cindy' ), $b2->vars);

        is($b1->table,$b2->table);
        is($b1->vars,$b2->vars);
        is($b1->build() , $b2->build() );
    }

    function testCloneWithAliasArguments() 
    {
        $b1 = new SQLBuilder\QueryBuilder;
        $b1->driver = $this->getDriver();
        $b1->driver->configure('placeholder','named');
        $b1->table('member');
        $b1->alias('m');
        $b1->where()
                ->equal('m.name','Cindy')
                ->equal('m.name','John');
        $sql = $b1->build();
        is( array( 
            ':m_name' => 'Cindy',
            ':m_name1' => 'John',
        ) , $b1->vars , 'check ->vars' );

        $b2 = clone $b1;
        is($b1->table,$b2->table);
        is($b1->vars,$b2->vars);
        is($b1->build() , $b2->build() );

        is( array( 
            ':m_name' => 'Cindy',
            ':m_name1' => 'John',
        ) , $b2->vars , 'check ->vars' );

        $b2->select('count(*)');
        $b2->where()
            ->equal('city','Taipei');

        $sql = $b2->build();
        is( array(
            ':m_name' => 'Cindy',
            ':m_name1' => 'John',
            ':city' => 'Taipei' ) , $b2->vars );

        ok($b1->build() != $b2->build() );
    }

    function testCloneWithJoinExpression() {
        $b1 = new SQLBuilder\QueryBuilder;
        $b1->driver = $this->getDriver();
        $b1->driver->configure('placeholder','named');
        $b1->table('member');
        $b1->alias('m');
        $b1->join('member_picture')
            ->alias('mp')
            ->on()
                ->equal('mp.member_id',array('m.id'));
        $b1->where()
            ->equal('name','Cindy')
            ->equal('name','John');
        $sql = $b1->build();
        ok($sql);
        ok($b1->vars);

        $b2 = clone $b1;
        $sql2 = $b2->build();
        is($sql,$sql2);
        ok( $b2->vars ); 
        ok( isset($b2->vars[':name']) ); 
        ok( isset($b2->vars[':name1']) ); 

        $b2->select('count(*)');
        $sql = $b2->build();
        ok($sql);
    }


    function testCloneWithBasicArguments() 
    {
        $b1 = new SQLBuilder\QueryBuilder;
        $b1->driver = $this->getDriver();
        $b1->driver->configure('placeholder','named');
        $b1->table('member');
        $b1->where()
            ->equal('name','Cindy')
            ->equal('name','John');
        $sql = $b1->build();

        ok(!empty($b1->vars));

        $b2 = clone $b1;
        is($b1->table,$b2->table);
        is($b1->vars,$b2->vars);
        is($b1->build() , $b2->build() );

        ok( isset($b1->vars[':name']) );
        ok( isset($b1->vars[':name1']) );
        ok( isset($b2->vars[':name']) );
        ok( isset($b2->vars[':name1']) );
        ok(!empty($b1->vars));
        ok(!empty($b2->vars));

        $b2->where()
            ->equal('city','Taipei');
        ok($b1->build() != $b2->build() );
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

    function testParameterConflict()
    {
        $sb = new SQLBuilder\QueryBuilder;
        $sb->table('member');
        $sb->driver = $this->getDriver();
        $sb->driver->configure('quote_column',true);
        $sb->driver->configure('placeholder','named');
        $sb->driver->quoter = array( $this->pdo, 'quote' );
        $sb->update(array(
            'name' => 'foo',
            'phone' => 'bar',
        ));
        $sb->where()
                ->equal('name','foo');
        $sql = $sb->build();
        ok( $sql );
        is("UPDATE member SET `name` = :name, `phone` = :phone WHERE `name` = :name1",$sql);

        $vars = $sb->getVars();
        is( 'foo' , $vars[':name'] );
        is( 'bar' , $vars[':phone'] );
        is( 'foo' , $vars[':name1'] );

        // is( 3 , $vars[':id'] );
        $stm = $this->pdo->prepare($sql);
        $stm->execute( $sb->vars );

        $this->noPDOError();
    }

    function testUpdateVars() 
    {
        $sb = new SQLBuilder\QueryBuilder;
        $sb->table('member');
        $sb->driver = $this->getDriver();
        $sb->driver->configure('quote_column',true);
        $sb->driver->configure('placeholder','named');
        $sb->driver->quoter = array( $this->pdo, 'quote' );
        $sb->update(array(
            'name' => 'foo',
            'phone' => 'bar',
        ));
        $sb->where()
                ->equal('id',3);
        $sql = $sb->build();
        ok( $sql );
        is("UPDATE member SET `name` = :name, `phone` = :phone WHERE `id` = :id",$sql);

        $vars = $sb->getVars();
        is( 'foo' , $vars[':name'] );
        is( 'bar' , $vars[':phone'] );
        is( 3 , $vars[':id'] );

        $stm = $this->pdo->prepare($sql);
        $stm->execute( $sb->vars );
        ok( $stm );
    }

    function testInsertVars() 
    {
        $sb = new SQLBuilder\QueryBuilder;
        $sb->table('member');
        $sb->driver = $this->getDriver();
        $sb->driver->configure('quote_column',true);
        $sb->driver->configure('placeholder','named');
        $sb->driver->quoter = array( $this->pdo, 'quote' );
        $sb->insert(array(
            'name' => 'foo',
            'phone' => 'bar',
        ));
        $sql = $sb->build();
        ok( $sql );
        is("INSERT INTO member ( `name`,`phone`) VALUES (:name,:phone)",$sql);

        $vars = $sb->getVars();
        is( 'foo' , $vars[':name'] );
        is( 'bar' , $vars[':phone'] );

        $stm = $this->pdo->prepare($sql);
        $stm->execute( $sb->vars );
        ok( $stm );
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

