<?php

class PlaceHolderSQLiteTest extends PHPUnit_PDO_TestCase
{

    public $dsn = 'sqlite::memory:';

    public $schema = array(
        'member_sqlite.sql'
    );

    public function getDriver()
    {
        $d = new SQLBuilder\Driver;
        $d->configure('driver','sqlite');
        $d->configure('placeholder','named');
        $d->quoter = array( $this->pdo, 'quote' );
        return $d;
    }

    public function testCasting()
    {
        $sb = new SQLBuilder\QueryBuilder;
        $sb->table('member');
        $sb->driver = $this->getDriver();
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
