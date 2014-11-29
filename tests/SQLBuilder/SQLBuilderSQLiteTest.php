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
    public $schema = array( 'tests/schema/member_sqlite.sql' );

    public $dsn = 'sqlite::memory:';

    public function getDriver()
    {
        return DriverFactory::create_sqlite_driver();
    }

    public function testWrapper()
    {
        $driver = $this->getDriver();
        $test = new TestQueryWrapper($driver);
        $ret = $test->delete()
                ->where()
                    ->equal('id',1)
                ->execute();
        is( 99, $ret );
    }

    public function testCloneWithNamedParameter()
    {
        $driver = $this->getDriver();
        $driver->setNamedParamMarker();

        $b1 = new SQLBuilder\QueryBuilder($driver);
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

    public function testCloneWithAliasArguments() 
    {
        $driver = $this->getDriver();
        $driver->setNamedParamMarker();

        $b1 = new SQLBuilder\QueryBuilder($driver);
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

    public function testCloneWithJoinExpression() {
        $driver = $this->getDriver();
        $driver->setNamedParamMarker();

        $b1 = new SQLBuilder\QueryBuilder($driver);
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

    public function testCloneWithBasicArguments() 
    {
        $driver = $this->getDriver();
        $driver->setNamedParamMarker();

        $b1 = new SQLBuilder\QueryBuilder($driver);
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

    public function testInsert()
    {
        $driver = $this->getDriver();
        $driver->setNoParamMarker();
        $sb = new SQLBuilder\QueryBuilder($driver);
        $sb->table('member');
        $sb->insert(array(
            'name' => 'foo',
            'phone' => 'bar',
        ));
        $sql = $sb->build();
        ok( $sql );
        is("INSERT INTO member ( name,phone) VALUES ('foo','bar')", $sql);
    }

    function testParameterConflict()
    {
        $driver = $this->getDriver();
        $driver->setQuoteColumn(true);
        $driver->setNamedParamMarker();
        $driver->setQuoter(array( $this->pdo, 'quote' ));

        $sb = new SQLBuilder\QueryBuilder($driver);
        $sb->table('member');
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

    public function testUpdateVars() 
    {
        $driver = $this->getDriver();
        $driver->setQuoteColumn();
        $driver->setNamedParamMarker();
        $driver->setQuoter(array( $this->pdo, 'quote' ));

        $sb = new SQLBuilder\QueryBuilder($driver);
        $sb->table('member');
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

    public function testInsertVars() 
    {
        $driver = $this->getDriver();
        $driver->setQuoteColumn(true);
        $driver->setNamedParamMarker();
        $driver->setQuoter(array( $this->pdo, 'quote' ));

        $sb = new SQLBuilder\QueryBuilder($driver);
        $sb->table('member');
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

    public function testQuoteInsert() 
    {
        $driver = $this->getDriver();
        $driver->setQuoteColumn(true);
        $driver->setNoParamMarker();
        $driver->setQuoter(array( $this->pdo, 'quote' ));

        $sb = new SQLBuilder\QueryBuilder($driver);
        $sb->table('member');
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

    public function testQuoteInsert2()
    {
        $driver = $this->getDriver();
        $driver->setQuoteColumn(true);
        $driver->setNoParamMarker();
        $driver->setQuoter(array( $this->pdo, 'quote' ));

        $sb = new SQLBuilder\QueryBuilder($driver);
        $sb->table('member');
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

        $driver = $this->getDriver();
        $driver->setQuoter(array( $this->pdo, 'quote' ));
        $driver->setNoParamMarker();

        $sb = new SQLBuilder\QueryBuilder($driver);
        $sb->table('member')->select('name')
            ->groupBy('country','name')
            ->order('name');
        $sql = $sb->build();

        is('SELECT name FROM member GROUP BY country,name ORDER BY name desc', $sql );
        
        $stm = $this->pdo->query( $sql );

        $row = $stm->fetch();
        $row2 = $stm->fetch();
        ok( $row );
        ok( $row2 );

        $sb->having()->equal('name','Taiwan');
        $sql = $sb->build();

        is( "SELECT name FROM member GROUP BY country,name HAVING name = 'Taiwan' ORDER BY name desc", $sql );
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

