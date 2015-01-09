<?php
namespace SQLBuilder\Driver;
use PDO;

class PDOSQLiteDriver extends SQLiteDriver
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

