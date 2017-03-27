<?php

namespace SQLBuilder\Universal\Traits;

trait RestrictTrait
{
    /**
     * @var boolean
     */
    protected $restrict;

    /**
     * @return $this
     */
    public function restrict()
    {
        $this->restrict = true;

        return $this;
    }

    /**
     * @return string
     */
    public function buildRestrictClause()
    {
        if ($this->restrict) {
            return ' RESTRICT';
        }

        return '';
    }
}
