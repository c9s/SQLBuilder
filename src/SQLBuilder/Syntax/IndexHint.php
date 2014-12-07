<?php
namespace SQLBuilder\Syntax;
use SQLBuilder\Syntax\Conditions;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use LogicException;

/**
 * MySQL IndexHint Support
 *
 * @see http://dev.mysql.com/doc/refman/5.7/en/index-hints.html
 */
class IndexHint implements ToSqlInterface
{
    public $hintType;

    public $indexList = array();

    public $for;

    public function __construct() {

    }

    public function useIndex($indexList)
    {
        $this->hintType = 'USE INDEX';
        $this->indexList = is_array($indexList) ? $indexList : func_get_args();
        return $this;
    }

    public function ignoreIndex($indexList)  { 
        $this->hintType = 'IGNORE INDEX';
        $this->indexList = is_array($indexList) ? $indexList : func_get_args();
        return $this;
    }

    public function forceIndex($indexList) { 
        $this->hintType = 'FORCE INDEX';
        $this->indexList = is_array($indexList) ? $indexList : func_get_args();
        return $this;
    }

    public function forJoin() { 
        $this->for = 'JOIN';
        return $this;
    }

    public function forOrderBy() { 
        $this->for = 'ORDER BY';
        return $this;
    }

    public function forGroupBy() { 
        $this->for = 'GROUP BY';
        return $this;
    }

    public function isDefined() {
        return $this->hintType !== NULL;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $sql = ' ' . $this->hintType;
        if ($this->for) {
            $sql .= ' FOR ' . $this->for;
        }
        return $sql . ' (' . join(',',$this->indexList) . ')';
    }

    static public function create() {
        return new self;
    }

}
