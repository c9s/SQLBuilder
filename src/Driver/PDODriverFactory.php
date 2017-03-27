<?php

namespace SQLBuilder\Driver;

use Exception;
use PDO;

/**
 * Class PDODriverFactory
 *
 * @package SQLBuilder\Driver
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class PDODriverFactory
{
    /**
     * @codeCoverageIgnore
     *
     * @param \PDO $pdo
     *
     * @return \SQLBuilder\Driver\PDOMySQLDriver|\SQLBuilder\Driver\PDOPgSQLDriver|\SQLBuilder\Driver\PDOSQLiteDriver
     * @throws \Exception
     */
    public static function create(PDO $pdo)
    {
        $driverName = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        switch ($driverName) {
            case 'mysql':
                return new PDOMySQLDriver($pdo);
                break;
            case 'pgsql':
                return new PDOPgSQLDriver($pdo);
                break;
            case 'sqlite':
                return new PDOSQLiteDriver($pdo);
                break;
            default:
                throw new Exception('Unsupported PDO driver');
                break;
        }
    }
}
