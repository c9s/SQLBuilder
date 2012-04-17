<?php
use SQLBuilder\Column;

class ColumnTest extends PHPUnit_Framework_TestCase
{
    function testTimestamp()
    {
        $createdOn = Column::create('created_on');
        $createdOn->type('timestamp')
            ->default(array('current_timestamp'))
            ->notNull();
        ok( $createdOn );
    }

}


class MigrationBuilderTest extends PHPUnit_Framework_TestCase
{

    function test()
    {
        $driver = DriverFactory::create_sqlite_driver();

        $builder = new SQLBuilder\MigrationBuilder( $driver );
        $sql = $builder->addColumn( 'product' , 
            SQLBuilder\Column::create('price')
                ->default(100)
        );
        
    }
}

