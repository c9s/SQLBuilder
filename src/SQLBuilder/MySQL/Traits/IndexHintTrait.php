<?php
namespace SQLBuilder\MySQL\Traits;
use SQLBuilder\Raw;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use SQLBuilder\MySQL\Syntax\IndexHint;

trait IndexHintTrait {

    /**
     * @var IndexHint[] stored by list
     */
    protected $indexHints = array();

    /**
     * @var <string>IndexHint stored by tableRef
     */
    protected $indexHintsByTableRef = array();

    public function indexHint($tableRef = NULL) {
        $hint = new IndexHint($this);
        $this->indexHints[] = $hint;
        if ($tableRef) {
            $this->indexHintsByTableRef[$tableRef][] = $hint;
        }
        return $hint;
    }

    public function definedIndexHint($tableRef)
    {
        return isset($this->indexHintsByTableRef[$tableRef]);
    }

    public function buildIndexHintClauseByTableRef($tableRef, BaseDriver $driver, ArgumentArray $args)
    {
        $sql = '';
        if (isset($this->indexHintsByTableRef[$tableRef])) {
            foreach($this->indexHintsByTableRef[$tableRef] as $hint) {
                $sql .= $hint->toSql($driver, $args);
            }
        }
        return $sql;
    }

    public function buildIndexHintClause(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = '';
        foreach($this->indexHints as $hint) {
            $sql .= $hint->toSql($driver, $args);
        }
        return $sql;
    }

}

