<?php


/**
 * Usage
 *
 *   class YourTest extends PHPUnit_PDO_TestCase
 *   {
 *
 *      // setup your database connection DSN (optional, default is sqlite memory)
 *      public $dsn = 'pgsql:tests';
 *
 *      // setup your database username (optional)
 *      public $user = 'postgres';
 *
 *      // setup your database password (optional)
 *      public $pass = 'postgres';
 *
 *      public $options = array( ... PDO connection options ... );
 *
 *      
 *      // provide your schema sql files
 *      public $schema = array( 
 *         'tests/schema/user.sql'
 *      );
 *
 *   }
 */
abstract class PHPUnit_PDO_TestCase extends PHPUnit_Framework_TestCase
{
    public $pdo;

    public $dsn = 'sqlite::memory:';
    public $user;
    public $pass;
    public $options;

    public $schema;

    public function noPDOError()
    {
        $err = $this->pdo->errorInfo();
        ok( $err[0] === '00000' );
    }

    public function getDSN()
    {
        return $this->dsn;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getPass()
    {
        return $this->pass;
    }

    public function getOptions()
    {
        return $this->options;
    }



    public function setup()
    {
        $this->pdo = new PDO(
            $this->getDSN(),  
            $this->getUser(),  
            $this->getPass(), 
            $this->getOptions() ?: null
        );

        // throw Exception on Error.
        $this->pdo->setAttribute( PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION );
        $this->setupSchema();
    }

    public function setupSchema()
    {
        if( $this->schema ) {
            foreach( $this->schema as $file ) {
                $content = file_get_contents($file);
                $statements = preg_split( '#;\s*$#', $content );
                foreach( $statements as $statement ) 
                    $this->queryOk($statement);
            }

        }


        // get schema SQL and send query
        $sqls = $this->schema();
        foreach( $sqls as $sql ) {
            $this->pdo->query($sql);
        }
        // well done!
    }

    public function testConnection() 
    {
        $this->assertInstanceOf('PDO', $this->pdo);
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


    /**
     * Test Query
     *
     * @param string $sql SQL statement.
     * @param array $args Arguments for executing SQL statement.
     */
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
