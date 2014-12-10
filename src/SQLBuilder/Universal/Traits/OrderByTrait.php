<?php
namespace SQLBuilder\Universal\Traits;
use SQLBuilder\Raw;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;

trait OrderByTrait {

    protected $orderByList = array();

    /**
    The Syntax:

    [ORDER BY {col_name | expr | position}
        [ASC | DESC], ...]

    > SELECT * FROM foo ORDER BY RAND(NOW()) LIMIT 1;
    > SELECT * FROM foo ORDER BY 1,2,3;

    > SELECT* FROM mytable ORDER BY
        LOCATE(CONCAT('.',`group`,'.'),'.9.7.6.10.8.5.');

    > SELECT `names`, `group`
        FROM my_table
        WHERE `group` IN (9,7,6,10,8,5)
        ORDER BY find_in_set(`group`,'9,7,6,10,8,5');

    @see http://dba.stackexchange.com/questions/5422/mysql-conditional-order-by-to-only-one-column
    @see http://dev.mysql.com/doc/refman/5.1/en/sorting-rows.html
    */
    public function orderBy($byExpr, $sorting = NULL) {
        $this->orderByList[] = array($byExpr, $sorting);
        return $this;
    }

    public function clearOrderBy() { 
        $this->orderByList = array();
    }

    public function setOrderBy(array $orderBy) {
        $this->orderByList = $orderBy;
        return $this;
    }


    public function buildOrderByClause(BaseDriver $driver, ArgumentArray $args) {
        if (empty($this->orderByList)) {
            return '';
        }

        $sql = '';
        foreach($this->orderByList as $orderBy) {
            if (is_array($orderBy)) {
                $sql .= ', ' . $orderBy[0] . ($orderBy[1] ? ' ' . strtoupper($orderBy[1]) : '');
            } elseif ($orderBy instanceof ToSqlInterface) {
                $sql .= ', ' . $orderBy->toSql($driver, $args);
            }
        }
        return ' ORDER BY' . ltrim($sql, ',');
    }
}


