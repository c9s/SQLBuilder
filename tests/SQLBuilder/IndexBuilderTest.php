<?php
use SQLBuilder\IndexBuilder;

class IndexBuilderTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        /*
        $driver = DriverFactory::create_pgsql_driver();

        $b = new IndexBuilder($driver);
        ok($b);

        // CREATE INDEX CONCURRENTLY on tags (name text_pattern_ops) WHERE media_count >= 100
        $b->create('index_name')
            ->unique()
            ->on( 'table_name' )
            ->using('rtree')
            ->concurrently()
            ->columns('foo','bar',array('name','text_pattern_ops'))
            ->where()
                ->greater('media_count', 500)
            ;

        $sql = $b->build();
        contains_ok('CREATE UNIQUE INDEX CONCURRENTLY index_name ON table_name', $sql);
        contains_ok('USING RTREE', $sql);
        contains_ok('"foo"', $sql);
        contains_ok('"bar"', $sql);
        contains_ok('"name" text_pattern_ops', $sql);
        contains_ok('WHERE "media_count" > :media_count', $sql);
         */
    }
}

