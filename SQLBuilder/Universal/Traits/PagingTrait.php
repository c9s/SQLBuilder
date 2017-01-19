<?php

namespace SQLBuilder\Universal\Traits;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

trait PagingTrait
{
    public $limit;

    public $offset;

    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function page($page, $pageSize = 10)
    {
        if ($page > 1) {
            $this->offset(($page - 1) * $pageSize);
        }
        return $this->limit($pageSize);
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function buildPagingClause(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = '';
        if ($this->limit) {
            $sql .= ' LIMIT '.intval($this->limit);
        }
        if ($this->offset) {
            $sql .= ' OFFSET '.intval($this->offset);
        }
        return $sql;
    }
}
