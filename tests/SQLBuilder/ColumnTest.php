<?php

use SQLBuilder\Column;
class ColumnTest extends PHPUnit_Framework_TestCase
{
    function test()
    {
        $createdOn = Column::create('created_on');
        $createdOn->type('timestamp')
            ->default(array('current_timestamp'))
            ->notNull();
        ok( $createdOn );
    }
}

