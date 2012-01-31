<?php

namespace SQLBuilder;

class JoinExpression
{

    public $driver;

    public $type;

    public $table;

    public $alias;

    public $onExpr = array();


    public $parent;

    function __construct($table,$type = 'LEFT')
    {
        $this->table = $table;
        $this->type = $type;
    }

    public function alias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    public function on()
    {
        $subexpr = new ExpressionGroup;
        $subexpr->parent = $this;
        $subexpr->driver = $this->driver;
        $this->onExpr[] = $subexpr;
        return $subexpr->createExpr(null);
    }

    public function toSql()
    {
        $sql = ' ' . $this->type 
            . ' JOIN '
            . $this->table;

        if( $this->alias )
            $sql .= ' ' . $this->alias;

        foreach( $this->onExpr as $expr ) {
            $sql .= ' ON ' . $expr->toSql();
        }
        return $sql;
    }
}


