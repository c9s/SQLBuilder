<?php
namespace SQLBuilder\Universal\Query;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\Universal\Expr\SelectExpr;

use SQLBuilder\Universal\Syntax\Conditions;
use SQLBuilder\Universal\Syntax\Join;
use SQLBuilder\Universal\Syntax\IndexHint;
use SQLBuilder\Universal\Syntax\Paging;

use SQLBuilder\Raw;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use Exception;
use InvalidArgumentException;

/**
 * > INSERT INTO tbl_name (a,b,c) VALUES (1,2,3),(4,5,6),(7,8,9);
 *
 *
 * @see MySQL Insert Statement http://dev.mysql.com/doc/refman/5.7/en/insert.html
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

    protected $options = array();

    protected $partitions;

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

    /*
    [LOW_PRIORITY | DELAYED | HIGH_PRIORITY]
     */
    public function option($opt)
    {
        if (is_array($opt)) {
            $this->options = $this->options + $opt;
        } else {
            $this->options = $this->options + func_get_args();
        }
        return $this;
    }

    public function options()
    {
        $this->options = func_get_args();
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

    public function partitions($partitions)
    {
        if (is_array($partitions)) {
            $this->partitions = new Partition($partitions);
        } else {
            $this->partitions = new Partition(func_get_args());
        }
        return $this;
    }

    public function buildPartitionClause(BaseDriver $driver, ArgumentArray $args)
    {
        if ($this->partitions) {
            return $this->partitions->toSql($driver, $args);
        }
        return '';
    }


    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $sql = 'INSERT';

        if (!empty($this->options)) {
            $sql .= ' ' . join(' ', $this->options);
        }

        $sql .= ' INTO ' . $driver->quoteTableName($this->intoTable);

        // append partition clause if needed.
        $sql .= $this->buildPartitionClause($driver, $args);

        $valuesClauses = array();
        $varCnt = 1;

        // build columns
        $columns = $this->getColumnNames($driver);

        foreach ($this->values as $values) {
            $deflatedValues = array();
            foreach ($values as $key => $value) {
                if ($value instanceof Raw) {

                    $deflatedValues[] = $value->getRaw();

                } elseif (!$value instanceof Bind && !$value instanceof ParamMarker) {

                    if (is_numeric($key)) {
                        $deflatedValues[] = $driver->deflate($driver->allocateBind($value), $args);
                    } else {
                        $deflatedValues[] = $driver->deflate(new Bind($key, $value), $args);
                    }

                } else {
                    $deflatedValues[] = $driver->deflate($value, $args);
                }
            }
            $valuesClauses[] = '(' . join(',', $deflatedValues) . ')';
        }

        $sql .= ' (' . join(',',$columns) . ')'
                . ' VALUES ' . join(', ', $valuesClauses) ;

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




