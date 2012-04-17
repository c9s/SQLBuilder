<?php
abstract class PHPUnit_PDO_TestCase extends PHPUnit_Framework_TestCase
{
    public $pdo;

    public $dsn = 'sqlite::memory:';
    public $user;
    public $pass;

    public function noPDOError()
    {
        $err = $this->pdo->errorInfo();
        ok( $err[0] === '00000' );
    }

    public function schema()
    {
        $sqls = array();
        $sqls[] =<<<SQL
        CREATE TABLE member ( 
            id integer primary key autoincrement, 
            name varchar(128) , 
            phone varchar(128) , 
            country varchar(128),
            confirmed boolean
        );
SQL;
        return $sqls;
    }

    public function setup()
    {
        $this->pdo = new PDO($this->dsn,$this->user,$this->pass);
        $this->pdo->setAttribute( PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
        $sqls = $this->schema();
        foreach( $sqls as $sql ) {
            $this->pdo->query($sql);
        }
    }

    public function queryOk($sql, $args = null)
    {
        if( $args ) {
            $stm = $this->pdo->prepare( $sql )->execute( $args );
        }
        else {
            $stm = $this->pdo->query( $sql );
        }
        $this->noPDOError();
        return $stm;
    }

}
