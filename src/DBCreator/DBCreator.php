<?php
namespace DBCreator;
use PDO;

class DBCreator
{

    public function create( $driverType , $options ) {
        switch( $driverType ) {
            case 'sqlite':
                return $this->createSqliteDb( $options );
                break;
            case 'mysql':
                return $this->createMysqlDb( $options );
                break;
            case 'pgsql':
                return $this->createPgsqlDb( $options );
                break;
            default:
                throw new Exception("Unknwon driver type");
        }
    }

    public function createSqliteDb( $options ) {
        $db = isset($options['database']) ? $options['database'] : ':memory:';
        $pdo = new PDO("sqlite:$db");
        $pdo->setAttribute( PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION );
        return $pdo;
    }

    public function createMysqlDb( $options ) {
        $db      = $options['database']; // database name is required
        $charset = @$options['charset']; // database name is required
        $pdo = new PDO("mysql:", @$options['user'] , @$options['password'] , @$options['attributes'] );
        $pdo->setAttribute( PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION );
        $sql = sprintf('CREATE DATABASE %s ', $db );
        if( $charset )
            $sql .= " CHARSET $charset";
        $result = $pdo->query($sql);
        return $pdo;
    }

    public function createPgsqlDb( $options ) {
        $db      = $options['database']; // database name is required
        $owner   = @$options['owner'];
        $template = @$options['template'];

        $pdo = new PDO("pgsql:", @$options['user'] , @$options['password'] , @$options['attributes'] );
        $pdo->setAttribute( PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION );
        $sql = 'CREATE DATABASE ' . $db;

        if( $owner )
            $sql .= ' OWNER ' . $owner;

        if( $template )
            $sql .= ' TEMPLATE ' . $template;

        $result = $pdo->query($sql);
        return $pdo;
    }

}

