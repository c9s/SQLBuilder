<?php
use SQLBuilder\Column;

class MigrationBuilderTest extends PHPUnit_PDO_TestCase
{

    function schema()
    {
        $sqls = array();;
        $sqls[] = ' create table members ( name text );';
        $sqls[] = ' create table member_books ( title text );';
        return $sqls;
    }

    function test()
    {
        $driver = DriverFactory::create_sqlite_driver();
        $builder = new SQLBuilder\MigrationBuilder( $driver );
        $sql = $builder->addColumn( 'members' , 
            Column::create('price')
                ->integer()
                ->notNull()
                ->default(100)
        );
        is( 'ALTER TABLE members ADD COLUMN price integer DEFAULT 100 NOT NULL', $sql );
        $this->queryOk( $sql );

        $sql = $builder->addColumn( 'members' , 
            Column::create('email')
                ->varchar(64)
        );
        is( 'ALTER TABLE members ADD COLUMN email varchar(64)', $sql );
        $this->queryOk( $sql );

        $sql = $builder->createIndex( 'members', 'email_index', 'email' );
        $this->queryOk( $sql );

        $sql = $builder->dropIndex( 'members', 'email_index' );
        $this->queryOk( $sql );

        // $sql = $builder->renameColumn( 'members' , 'email' , 'email_new' );
        // var_dump( $sql ); 
    }

    function testForeignKey() 
    {
        $driver = DriverFactory::create_mysql_driver();
        $builder = new SQLBuilder\MigrationBuilder( $driver );
        $sql = $builder->addForeignKey('books','author_id','authors','id');
        is('ALTER TABLE books ADD FOREIGN KEY (author_id) REFERENCES books(`id`)',$sql);
    }

    function test2()
    {
        $driver = DriverFactory::create_sqlite_driver();
        $builder = new SQLBuilder\MigrationBuilder( $driver );
        $sql = $builder->addColumn( 'members' , 
            SQLBuilder\Column::create('views')
                ->integer()
                ->default(0)
        );
        ok( $sql );
        $this->queryOk( $sql );

        $sql = $builder->dropColumn( 'members' , 'views' );
        ok( $sql );
        // $this->queryOk( $sql );
        is( 'ALTER TABLE members DROP COLUMN views' , $sql );
    }

}


