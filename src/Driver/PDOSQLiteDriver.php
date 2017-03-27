<?php

namespace SQLBuilder\Driver;

use PDO;

/**
 * Class PDOSQLiteDriver
 *
 * @package SQLBuilder\Driver
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class PDOSQLiteDriver extends SQLiteDriver
{
    /**
     * @var \PDO
     */
    public $pdo;

    /**
     * PDOSQLiteDriver constructor.
     *
     * @param \PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param $str
     *
     * @return string
     */
    public function quote($str)
    {
        return $this->pdo->quote($str);
    }

    /**
     * @return mixed
     */
    public function getDriverName()
    {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->pdo;
    }
}
