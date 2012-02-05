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
        $pdo = new PDO('sqlite::memory');

        $sb = new SQLBuilder\QueryBuilder;
        $sb->table('member');
        $sb->driver = $this->getDriver();
        $sb->insert(array(
            'foo' => 'foo',
            'bar' => 'bar',
        ));
        $sql = $sb->build();

        
    }
}

