<?php
namespace SQLBuilder\Syntax;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;
use SQLBuilder\ToSqlInterface;
use LogicException;

class Paging implements ToSqlInterface
{
    public $limit;

    public $offset;

    public function __construct($limit, $offset = NULL)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function getOffset() {
        return $this->offset;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        if ($this->offset) {
            return ' LIMIT ' . intval($this->limit) . ' OFFSET ' . $this->offset;
        }
        return ' LIMIT ' . intval($this->limit);
    }
}



