<?php
namespace DBCreator;
use PDO;
use Exception;

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

    public function createConnection($type,$options) {
        switch($type) {
        case 'sqlite':
            $db = isset($options['database']) ? $options['database'] : ':memory:';
            $pdo = new PDO("sqlite:$db");
            $pdo->setAttribute( PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION );
            return $pdo;
            break;
        case 'mysql':
            $pdo = new PDO("mysql:", @$options['user'] , @$options['password'] , @$options['attributes'] );
            $pdo->setAttribute( PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION );
            return $pdo;
            break;
        case 'pgsql':
            $pdo = new PDO("pgsql:", @$options['user'] , @$options['password'] , @$options['attributes'] );
            $pdo->setAttribute( PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION );
            return $pdo;
            break;
        default:
            throw new Exception("Unsupported driver type");
        }
    }

    public function createSqliteDb( $options ) {
        return $this->createConnection('sqlite',$options);
    }

    public function createMysqlDb( $options ) {
        $pdo = $this->createConnection( 'mysql', $options );

        $db      = $options['database']; // database name is required
        $charset = @$options['charset']; // database name is required
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
        $pdo = $this->createConnection( 'pgsql' , $options );

        $sql = 'CREATE DATABASE ' . $db;
        if( $owner )
            $sql .= ' OWNER ' . $owner;

        if( $template )
            $sql .= ' TEMPLATE ' . $template;

        $result = $pdo->query($sql);
        return $pdo;
    }


}

