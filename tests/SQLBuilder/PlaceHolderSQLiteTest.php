<?php

class PlaceHolderSQLiteTest extends PHPUnit_PDO_TestCase
{

    public $dsn = 'sqlite::memory:';

    public $schema = array(
        'member_sqlite.sql'
    );

    public function getDriver()
    {
        $d = new SQLBuilder\Driver\SQLiteDriver;
        $d->setNamedParamMarker();
        $d->quoter = array( $this->pdo, 'quote' );
        return $d;
    }

    public function testCasting()
    {
        $driver = $this->getDriver();

        $sb = new SQLBuilder\QueryBuilder($driver);
        $sb->table('member');
        $sb->select('*');
        $sb->where()
            ->equal('confirmed' , true);
        $sql = $sb->build();
        $vars = $sb->getVars();
        ok( $vars );
        ok( $sql );
        $this->queryOk( $sql , $vars );
    }

}
