<?php
namespace SQLBuilder;
use SQLBuilder\Expression;
use SQLBuilder\ExpressionGroup;

class BaseExpression
{
    public $driver;

    /**
     * back to top parent 
     */
    public function back()
    {
        $p = $this;
        while( property_exists($p,'parent') && $p = $p->parent ) {
            if( ! property_exists($p,'parent') || ! $p->parent )
                return $p;
        }
    }

    public function createGroupExpr($op = 'AND')
    {
        $subexpr = new ExpressionGroup;
        $subexpr->parent = $this;
        $subexpr->parentOp = $op;
        $subexpr->driver = $this->driver;
        $this->childs[] = $subexpr;
        return $subexpr;
    }

    public function createExpr($op = 'AND')
    {
        $subexpr = new Expression;
        $subexpr->parent = $this;
        $subexpr->parentOp = $op;
        $subexpr->driver = $this->driver;
        $this->childs[] = $subexpr;
        return $subexpr;
    }
}

