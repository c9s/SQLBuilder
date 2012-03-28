<?php

abstract class PHPUnit_PDO_TestCase extends PHPUnit_Framework_TestCase
{
    public $pdo;

    public function noPDOError()
    {
        $err = $this->pdo->errorInfo();
        ok( $err[0] === '00000' );
    }

    public function setup()
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->query( 'CREATE TABLE member ( 
            id integer primary key autoincrement, 
            name varchar(128) , 
            phone varchar(128) , 
            country varchar(128),
            confirmed boolean
        );' );
        $this->pdo->setAttribute( PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
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
