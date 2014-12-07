<?php
namespace SQLBuilder\Query;
use Exception;
use SQLBuilder\RawValue;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use SQLBuilder\Expr\SelectExpr;
use SQLBuilder\Syntax\Conditions;
use SQLBuilder\Syntax\Join;
use SQLBuilder\Syntax\IndexHint;
use SQLBuilder\Syntax\Paging;

class InsertQuery
{

    protected $values = array();

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

    public function buildValuesClause(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = '';
        $clauses = array();
        $columns = array();
        foreach($this->values as $values) {
            $values = array();
            foreach($values as $k => $v ) {
                if (is_integer($k)) {
                    $k = $v;
                }

                $columns[] = $driver->quoteColumn($k);
                $values[] = $v[0];

                if (is_array($v)) {
                    // Just interpolate the raw value
                } elseif ($v instanceof RawValue) {
                    $columns[] = $driver->quoteColumn($k);
                    $values[] = $v;
                } else {
                    $columns[] = $driver->quoteColumn($k);
                    $newK = $this->setPlaceHolderVar( $k , $v );
                    $values[] = $driver->getParamMarker($newK);
                }

                /*
                foreach( $this->insert as $k => $v ) {
                    if (is_integer($k)) {
                        $k = $v;
                    }
                    $columns[] = $this->driver->quoteColumn( $k );
                    $values[]  = $this->driver->deflate($v);
                }
                */
            }
            $clause = join(',', $columns);
            $clauses[] = $clause;
        }

        $sql = 'INSERT INTO ' . $this->driver->quoteTableName($this->table)
            . ' ('
            . join(',',$columns) 
            . ') VALUES (' 
            . join(',', $values ) 
            . ')';
            ;

        if ($this->returning && ($this->driver instanceof PgSQLDriver) ) {
            $sql .= ' RETURNING ' . $this->driver->quoteColumn($this->returning);
        }
        return $sql;
    }

    public function buildReturningClause(BaseDriver $driver)
    {
        if ($driver instanceof PgSQLDriver) {
            return 'RETURNING ' . $this->returning;
        }
        return '';
    }
}




