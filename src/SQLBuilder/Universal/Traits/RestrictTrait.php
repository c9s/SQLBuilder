<?php
namespace SQLBuilder\Universal\Traits;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

trait RestrictTrait
{
    protected $restrict;

    public function restrict() {
        $this->restrict = true;
        return $this;
    }

    public function buildRestrictClause() {
        if ($this->restrict) {
            return ' RESTRICT';
        }
        return '';
    }
}





