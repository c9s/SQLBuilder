<?php

namespace SQLBuilder\Universal\Traits;

trait IfExistsTrait
{
    protected $ifExists = false;

    public function ifExists()
    {
        $this->ifExists = true;

        return $this;
    }

    public function buildIfExistsClause()
    {
        if ($this->ifExists) {
            return ' IF EXISTS';
        }

        return '';
    }
}
