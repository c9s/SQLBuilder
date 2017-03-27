<?php

namespace SQLBuilder\Universal\Traits;

trait IfExistsTrait
{
    /**
     * @var bool
     */
    protected $ifExists = false;

    /**
     * @return $this
     */
    public function ifExists()
    {
        $this->ifExists = true;

        return $this;
    }

    /**
     * @return string
     */
    public function buildIfExistsClause()
    {
        if ($this->ifExists) {
            return ' IF EXISTS';
        }

        return '';
    }
}
