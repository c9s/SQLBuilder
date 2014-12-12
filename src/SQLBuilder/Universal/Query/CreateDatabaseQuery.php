<?php
namespace SQLBuilder\Universal\Query;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Exception\CriticalIncompatibleUsageException;
use SQLBuilder\Exception\IncompleteSettingsException;
use SQLBuilder\Exception\UnsupportedDriverException;
use SQLBuilder\PgSQL\Traits\ConcurrentlyTrait;
use SQLBuilder\Universal\Traits\IfExistsTrait;

class CreateDatabaseQuery implements ToSqlInterface
{
    use IfExistsTrait;

    protected $dbName;



    /**
     * PostgreSQL ONLY
     */
    protected $owner;

    protected $template;

    protected $encoding;

    protected $ctype;

    protected $collate;

    protected $tablespace;

    protected $connectionLimit;


    /**
     * MySQL
     */
    protected $characterSet;


    public function name($name) {
        $this->dbName = $name;
        return $this;
    }

    public function create($name) {
        $this->dbName = $name;
        return $this;
    }

    public function owner($owner) {
        $this->owner = $owner;
        return $this;
    }

    public function template($template) {
        $this->template = $template;
        return $this;
    }

    public function encoding($encoding) {
        $this->encoding = $encoding;
        return $this;
    }

    public function ctype($ctype) {
        $this->ctype = $ctype;
        return $this;
    }

    public function collate($collate) {
        $this->collate = $collate;
        return $this;
    }

    public function tablespace($tablespace) {
        $this->tablespace = $tablespace;
        return $this;
    }

    public function connectionLimit($connectionLimit) {
        $this->connectionLimit = $connectionLimit;
        return $this;
    }


    /**
     * @see http://dev.mysql.com/doc/refman/5.0/en/charset.html
     */
    public function characterSet($characterSet) {
        $this->characterSet = $characterSet;
        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = 'CREATE DATABASE ' . $driver->quoteIdentifier($this->dbName);

        if ($driver instanceof MySQLDriver) {

            
            if ($this->characterSet) {
                $sql .= ' CHARACTER SET ' . $driver->quote($this->characterSet);
            }
            if ($this->collate) {
                $sql .= ' COLLATE ' . $driver->quote($this->collate);
            }

        } elseif ($driver instanceof PgSQLDriver) {

            /**
            * PostgreSQL properties
            */
            if ($this->owner) {
                $sql .= ' OWNER ' . $driver->quote($this->owner);
            }
            if ($this->template) {
                $sql .= ' TEMPLATE ' . $driver->quote($this->template);
            }
            if ($this->encoding) {
                $sql .= ' ENCODING ' . $driver->quote($this->encoding);
            }
            if ($this->collate) {
                $sql .= ' LC_COLLATE ' . $driver->quote($this->collate);
            }
            if ($this->ctype) {
                $sql .= ' LC_CTYPE ' . $driver->quote($this->ctype);
            }
            if ($this->tablespace) {
                $sql .= ' TABLESPACE ' . $driver->quote($this->tablespace);
            }
            if ($this->connectionLimit) {
                $sql .= ' CONNECTION LIMIT ' . $this->connectionLimit;
            }
        }
        return $sql;
    }
}



