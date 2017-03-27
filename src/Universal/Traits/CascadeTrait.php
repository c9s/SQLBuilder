<?php

namespace SQLBuilder\Universal\Traits;

trait CascadeTrait
{
    /**
     * @var bool
     */
    protected $cascade;

    /**
     * @return $this
     */
    public function cascade()
    {
        $this->cascade = true;

        return $this;
    }

    /**
     * @return string
     */
    public function buildCascadeClause()
    {
        if ($this->cascade) {
            return ' CASCADE';
        }

        return '';
    }
}
