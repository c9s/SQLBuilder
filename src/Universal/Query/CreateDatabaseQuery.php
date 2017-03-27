<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Universal\Traits\IfExistsTrait;

/**
 * Class CreateDatabaseQuery
 *
 * @package SQLBuilder\Universal\Query
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class CreateDatabaseQuery implements ToSqlInterface
{
    use IfExistsTrait;

    protected $dbName;

    /**
     * PostgreSQL ONLY.
     */
    protected $owner;

    protected $template;

    protected $encoding;

    protected $ctype;

    protected $collate;

    protected $tablespace;

    protected $connectionLimit;

    /**
     * MySQL.
     */
    protected $characterSet;

    protected $ifNotExists = false;

    /**
     * CreateDatabaseQuery constructor.
     *
     * @param null $name
     */
    public function __construct($name = null)
    {
        $this->dbName = $name;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function create($name)
    {
        $this->dbName = $name;

        return $this;
    }

    /**
     * @param $owner
     *
     * @return $this
     */
    public function owner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @param $template
     *
     * @return $this
     */
    public function template($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @param $encoding
     *
     * @return $this
     */
    public function encoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    public function ctype($ctype)
    {
        $this->ctype = $ctype;

        return $this;
    }

    /**
     * @param $collate
     *
     * @return $this
     */
    public function collate($collate)
    {
        $this->collate = $collate;

        return $this;
    }

    /**
     * @param $tablespace
     *
     * @return $this
     */
    public function tablespace($tablespace)
    {
        $this->tablespace = $tablespace;

        return $this;
    }

    /**
     * @param $connectionLimit
     *
     * @return $this
     */
    public function connectionLimit($connectionLimit)
    {
        $this->connectionLimit = $connectionLimit;

        return $this;
    }

    /**
     * @see http://dev.mysql.com/doc/refman/5.0/en/charset.html
     *
     * @param $characterSet
     *
     * @return $this
     */
    public function characterSet($characterSet)
    {
        $this->characterSet = $characterSet;

        return $this;
    }

    /**
     * @return $this
     */
    public function ifNotExists()
    {
        $this->ifNotExists = true;

        return $this;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = 'CREATE DATABASE';

        if ($this->ifNotExists && $driver instanceof MySQLDriver) {
            $sql .= ' IF NOT EXISTS';
        }

        $sql .= ' ' . $driver->quoteIdentifier($this->dbName);

        if ($driver instanceof MySQLDriver) {
            if ($this->characterSet) {
                $sql .= ' CHARACTER SET ' . $driver->quote($this->characterSet);
            }
            if ($this->collate) {
                $sql .= ' COLLATE ' . $driver->quote($this->collate);
            }
        } elseif ($driver instanceof PgSQLDriver) {

            /*
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
