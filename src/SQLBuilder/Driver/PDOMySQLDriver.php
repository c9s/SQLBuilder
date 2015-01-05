<?php
namespace SQLBuilder\Driver;
use PDO;

class PDOMySQLDriver extends MySQLDriver
{
    public $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function quote($str)
    {
        return $this->pdo->quote($str);
    }
}

