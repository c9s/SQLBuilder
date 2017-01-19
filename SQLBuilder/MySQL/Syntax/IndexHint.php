<?php

namespace SQLBuilder\MySQL\Syntax;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\ToSqlInterface;
use BadMethodCallException;
use SQLBuilder\Exception\IncompleteSettingsException;

/**
 * MySQL IndexHint Support.
 *
 * @see http://dev.mysql.com/doc/refman/5.7/en/index-hints.html
 */
class IndexHint implements ToSqlInterface
{
    public $hintType;

    public $indexList = array();

    protected $for;

    protected $parent;

    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    public function useIndex($indexList)
    {
        $this->hintType = 'USE INDEX';
        $this->indexList = is_array($indexList) ? $indexList : func_get_args();

        return $this;
    }

    public function ignoreIndex($indexList)
    {
        $this->hintType = 'IGNORE INDEX';
        $this->indexList = is_array($indexList) ? $indexList : func_get_args();

        return $this;
    }

    public function forceIndex($indexList)
    {
        $this->hintType = 'FORCE INDEX';
        $this->indexList = is_array($indexList) ? $indexList : func_get_args();

        return $this;
    }

    public function forJoin()
    {
        $this->for = 'JOIN';

        return $this;
    }

    public function forOrderBy()
    {
        $this->for = 'ORDER BY';

        return $this;
    }

    public function forGroupBy()
    {
        $this->for = 'GROUP BY';

        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        if (!$this->hintType) {
            throw new IncompleteSettingsException('Hint type undefined.');
        }

        $sql = ' '.$this->hintType;
        if ($this->for) {
            $sql .= ' FOR '.$this->for;
        }

        return $sql.' ('.implode(',', $this->indexList).')';
    }

    public function __call($m, $a)
    {
        if ($this->parent) {
            return call_user_func_array(array($this->parent, $m), $a);
        }
        throw new BadMethodCallException("Undefined $m method called.");
    }
}
