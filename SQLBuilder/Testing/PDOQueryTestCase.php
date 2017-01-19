<?php

namespace SQLBuilder\Testing;

use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use PDO;
use Exception;
use PDOException;

/**
 * @codeCoverageIgnore
 *
 *
 * @class PHPUnit_PDO_TestCase
 *
 * @cover
 *
 * @author Yo-An Lin <yoanlin93@gmail.com>
 *
 * @code
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
abstract class PDOQueryTestCase extends QueryTestCase
{
    /**
     * @var PDO PDO connection handle
     */
    public $pdo;

    /**
     * @var string database connection string (DSN)
     */
    public $dsn;

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
     * @var string Schema directory path
     */
    public $schemaDir = 'tests/schema';

    /**
     * @var array Fixture files
     */
    public $fixture;

    /**
     * @var string Fixture directory path
     */
    public $fixtureDir = 'tests/fixture';

    public $driverType = 'MySQL';

    public function getConnection()
    {
        return $this->pdo;
    }

    protected function assertNoPDOError(PDO $conn, $message = null)
    {
        $err = $conn->errorInfo();
        $this->assertEquals('00000', $err[0], $message);
    }

    public function getCurrentDriverType()
    {
        return strtoupper($this->driverType);
    }

    public function getCurrentDSN()
    {
        return $this->dsn ?: getenv(strtoupper($this->driverType).'_DSN');
    }

    public function getCurrentUser()
    {
        return $this->user ?: getenv(strtoupper($this->driverType).'_USER');
    }

    public function getCurrentPass()
    {
        return $this->pass ?: getenv(strtoupper($this->driverType).'_PASS');
    }

    public function getDriverDSN($driverType)
    {
        return getenv(strtoupper($driverType).'_DSN');
    }

    public function getDriverUser($driverType)
    {
        return getenv(strtoupper($driverType).'_USER');
    }

    public function getDriverPass($driverType)
    {
        return getenv(strtoupper($driverType).'_PASS');
    }

    public function createConnection($driverType)
    {
        $dsn = $this->getDriverDSN($driverType);
        $user = $this->getDriverUser($driverType);
        $pass = $this->getDriverPass($driverType);
        $options = $this->getOptions() ?: null;

        if ($dsn && $user && $pass) {
            return new PDO($dsn, $user, $pass, $options);
        } elseif ($dsn && $user) {
            return new PDO($dsn, $user);
        } elseif ($dsn) {
            return new PDO($dsn);
        } else {
            throw new Exception("Can't create connection for $driverType, missing configurations.");
        }
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getDb()
    {
        return $this->pdo;
    }

    public function setUp()
    {
        if (!extension_loaded('pdo')) {
            return skip('pdo extension is required');
        }

        // XXX: check pdo driver
#          if( ! extension_loaded('pdo_pgsql') ) 
#              return skip('pdo pgsql required');

        if ($driverType = $this->getCurrentDriverType()) {
            $this->pdo = $this->createConnection($driverType);
        } else {
            throw new Exception('Please define driver type for testing.');
        }

        if (!$this->pdo) {
            throw new Exception('Can not create PDO connection: '.get_class($this));
        }

        // throw Exception on Error.
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setupSchema();

        $this->assertNotEmpty($this->pdo);
    }

    public function setupSchema()
    {
        // get schema file (if we provide them)
        if ($this->schema) {
            foreach ($this->schema as $file) {

                // try to find schema file in schema directory
                if (!file_exists($file)) {
                    if (file_exists($this->schemaDir.DIRECTORY_SEPARATOR.$file)) {
                        $file = $this->schemaDir.DIRECTORY_SEPARATOR.$file;
                    } else {
                        throw new Exception("schema file $file not found.");
                    }
                }
                $content = file_get_contents($file);

                $statements = preg_split('#;\s*$#ms', $content);
                foreach ($statements as $statement) {
                    $this->queryOk(trim($statement));
                }
            }
        }

        // get schema from class method, which is SQL. 
        // then send query
        if ($sqls = $this->schema()) {
            foreach ($sqls as $sql) {
                $this->pdo->query($sql);
            }
        }
        // well done!
    }

    public function setupFixture()
    {
        if ($this->fixture) {
            foreach ($this->fixture as $file) {
                if (!file_exists($file)) {
                    if (file_exists($this->fixtureDir.DIRECTORY_SEPARATOR.$file)) {
                        $file = $this->fixtureDir.DIRECTORY_SEPARATOR.$file;
                    } else {
                        throw new Exception("fixture file $file not found.");
                    }
                }

                $content = file_get_contents($file);
                $statements = preg_split('#;\s*$#', $content);
                foreach ($statements as $statement) {
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

    public function assertQuery(ToSqlInterface $query, $message = null)
    {
        $driver = $this->createDriver();
        $args = new ArgumentArray();
        $sql = $query->toSql($driver, $args);
        $this->queryOk($sql, $args->toArray(), $message);

        return $args;
    }

    public function assertDriverQuery(BaseDriver $driver, ToSqlInterface $query)
    {
        $args = new ArgumentArray();
        $sql = $query->toSql($driver, $args);

        if ($driver instanceof MySQLDriver) {
            $conn = $this->createConnection('mysql');
        } elseif ($driver instanceof PgSQLDriver) {
            $conn = $this->createConnection('pgsql');
        } elseif ($driver instanceof SQLiteDriver) {
            $conn = $this->createConnection('sqlite');
        }

        $stm = $conn->prepare($sql);

        $err = $conn->errorInfo();
        $this->assertEquals('00000', $err[0], var_export($err, true).' SQL: '.$sql);

        $stm->execute($args->toArray());

        $err = $conn->errorInfo();
        $this->assertEquals('00000', $err[0], var_export($err, true).' SQL: '.$sql);

        return $args;
    }

    /*
    public function assertSqlQueries(ToSqlInterface $query, array $defines) {
        foreach($defines as $define) {
            list($driver, $expectedSQL) = $define;
            $args = new ArgumentArray;
            $sql = $query->toSql($driver, $args);
            $this->assertEquals($expectedSQL, $sql);
            // $this->assertDriverQuery($driver, $query);
        }
    }
     */

    public function query($sql, array $args = array())
    {
        if ($args) {
            $stm = $this->pdo->prepare($sql)->execute($args);
        } else {
            $stm = $this->pdo->query($sql);
        }
        $this->assertNoPDOError($this->pdo, $sql);

        return $stm;
    }

    /**
     * Test Query.
     *
     * @param string $sql  SQL statement.
     * @param array  $args Arguments for executing SQL statement.
     */
    public function queryOk($sql, array $args = array(), $message = null)
    {
        try {
            if ($args) {
                $stm = $this->pdo->prepare($sql)->execute($args);
            } else {
                $stm = $this->pdo->query($sql);
            }
            $this->assertNoPDOError($this->pdo, $message ?: $sql);

            return $stm;
        } catch (PDOException $e) {
            fprintf(STDERR, "\n");
            fprintf(STDERR, get_class($e)."\n");
            fprintf(STDERR, $e->getMessage()."\n");
            fprintf(STDERR, "SQL: $sql\n");
            throw $e;
        }
    }

    protected function executeOk($sql, $args)
    {
        $stm = $this->pdo->prepare($sql);
        $err = $this->pdo->errorInfo();

        ok(!$err[1], $err[0]);
        $stm->execute($args);

        $err = $this->pdo->errorInfo();
        ok(!$err[1]);

        return $stm;
    }

    protected function recordOk($sql)
    {
        $stm = $this->queryOk($sql);
        $row = $stm->fetch();
        $this->assertNotEmpty($row);

        return $row;
    }
}
