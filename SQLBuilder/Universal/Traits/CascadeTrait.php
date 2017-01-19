<?php

namespace SQLBuilder\Universal\Traits;

trait CascadeTrait
{
    protected $cascade;

    public function cascade()
    {
        $this->cascade = true;

        return $this;
    }

    public function buildCascadeClause()
    {
        if ($this->cascade) {
            return ' CASCADE';
        }

        return '';
    }
}
