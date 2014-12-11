<?php
namespace SQLBuilder\Universal\Traits;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

trait CascadeTrait
{
    protected $cascade;

    public function cascade() {
        $this->cascade = true;
        return $this;
    }

    public function buildCascadeClause() {
        if ($this->cascade) {
            return ' CASCADE';
        }
        return '';
    }

}





