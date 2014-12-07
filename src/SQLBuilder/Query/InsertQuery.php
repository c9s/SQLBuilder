<?php
namespace SQLBuilder\Query;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\Expr\SelectExpr;

use SQLBuilder\Syntax\Conditions;
use SQLBuilder\Syntax\Join;
use SQLBuilder\Syntax\IndexHint;
use SQLBuilder\Syntax\Paging;

use SQLBuilder\RawValue;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use Exception;
use InvalidArgumentException;

/**
 * > INSERT INTO tbl_name (a,b,c) VALUES (1,2,3),(4,5,6),(7,8,9);
 */
class InsertQuery
{

    /**
     * insert into table
     *
     * @param string table name.
     */
    protected $intoTable;

    protected $values = array();

    static public $BindValues = TRUE;

    /**
     * Should return result when updating or inserting?
     *
     * when this flag is set, the primary key will be returned.
     *
     * @var string
     */
    protected $returning;


    public function insert(array $values)
    {
        $this->values[] = $values;
        return $this;
    }

    public function into($table) {
        $this->intoTable = $table;
        return $this;
    }

    public function getColumnNames(BaseDriver $driver) {
        return array_map(function($col) use($driver) { 
            if (is_numeric($col)) {
                throw new InvalidArgumentException("Invalid column name: $col");
            }
            return $driver->quoteColumn($col);
        }, array_keys($this->values[0]));
    }

    public function returning($returningColumns) {
        $this->returning = $returningColumns;
        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $sql = '';
        $valuesClauses = array();

        $varCnt = 1;

        // build columns
        $columns = $this->getColumnNames($driver);

        foreach ($this->values as $values) {
            $deflatedValues = array();
            foreach ($values as $value) {
                if ($value instanceof RawValue) {
                    $deflatedValues[] = $value->getRawValue();
                } elseif (static::$BindValues && (!$value instanceof Bind && !$value instanceof ParamMarker)) {
                    $deflatedValues[] = $driver->deflate(new Bind("p" . ($varCnt++), $value), $args);
                } else {
                    $deflatedValues[] = $driver->deflate($value, $args);
                }
            }
            $valuesClauses[] = '(' . join(',', $deflatedValues) . ')';
        }

        $sql = 'INSERT INTO ' . $driver->quoteTableName($this->intoTable)
            . ' (' . join(',',$columns) . ')'
            . ' VALUES '
            . join(', ', $valuesClauses);
            ;

        // Check if RETURNING is supported
        if ($this->returning && ($driver instanceof PgSQLDriver) ) {
            // The "RETURNING" can be an array.
            if (is_array($this->returning)) {
                $sql .= ' RETURNING ' . join(',', $driver->quoteColumns($this->returning));
            } else {
                $sql .= ' RETURNING ' . $driver->quoteColumn($this->returning);
            }
        }
        return $sql;
    }
}




