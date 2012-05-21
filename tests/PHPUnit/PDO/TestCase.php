<?php

/**
 * @class PHPUnit_PDO_TestCase
 *
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
 *
 *      // optional
 *      public $options = array( ... PDO connection options ... );
 *
 *      
 *      // provide your schema sql files
 *      public $schema = array( 
 *         'tests/schema/user.sql'
 *      );
 *
 *      // provide your fixture sql files
 *      public $fixture = array( 
 *          'tests/fixtures/file.sql',
 *      );
 *
 *   }
 */
abstract class PHPUnit_PDO_TestCase extends PHPUnit_Framework_TestCase
{

    /**
     * @var PDO PDO connection handle
     */
    public $pdo;


    /**
     * @var string database connection string (DSN)
     */
    public $dsn = 'sqlite::memory:';

    /**
     * @var string database username
     */
    public $user;

    /**
     * @var string database password
     */
    public $pass;


    /**
     * @var array PDO connection options
     */
    public $options;


    /**
     * @var array Schema files
     */
    public $schema;


    /**
     * @var array Fixture files
     */
    public $fixture;

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
        // get schema file (if we provide them)
        if( $this->schema ) {
            foreach( $this->schema as $file ) {
                $content = file_get_contents($file);
                $statements = preg_split( '#;\s*$#', $content );
                foreach( $statements as $statement ) 
                    $this->queryOk($statement);
            }

        }

        // get schema from class method, which is SQL. 
        // then send query
        if( $sqls = $this->schema() ) {
            foreach( $sqls as $sql ) {
                $this->pdo->query($sql);
            }
        }
        // well done!
    }

    public function setupFixture()
    {
        if( $this->fixture ) {
            foreach( $this->fixture as $file {
                $content = file_get_contents($file);
                $statements = preg_split( '#;\s*$#', $content );
                foreach( $statements as $statement ) {
                    $this->queryOk($statement);
                }
            }
        }
    }

    public function testConnection() 
    {
        $this->assertInstanceOf('PDO', $this->pdo);
    }

    public function schema()
    {
        return;
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
