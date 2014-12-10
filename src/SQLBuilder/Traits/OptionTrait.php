<?php
namespace SQLBuilder\Traits;
use SQLBuilder\Raw;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use SQLBuilder\Syntax\Join;

trait OptionTrait
{

    /**
     * MySQL Select Options:
     *
     *   [ALL | DISTINCT | DISTINCTROW ]
     *   [HIGH_PRIORITY]
     *   [MAX_STATEMENT_TIME = N]
     *   [STRAIGHT_JOIN]
     *   [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
     *   [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
     *
     * $this->option([ 'SQL_SMALL_RESULT', 'SQL_CALC_FOUND_ROWS', 'MAX_STATEMENT_TIME = N']);
     */
    public function option($selectOption) 
    {
        if (is_array($selectOption)) {
            $this->options = $this->options + $selectOption;
        } else {
            $this->options = $this->options + func_get_args();
        }
        return $this;
    }

    public function options() {
        $this->options = func_get_args();
        return $this;
    }

    public function buildOptionClause() 
    {
        if (empty($this->options)) {
            return '';
        }
        return ' ' . join(' ', $this->options);
    }


}

