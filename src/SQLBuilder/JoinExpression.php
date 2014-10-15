<?php

namespace SQLBuilder;

class JoinExpression extends BaseExpression
{
    public $type;

    /**
     * @var string table name
     */
    public $table;


    /**
     * @var string alias
     */
    public $alias;

    public $onExpr = array();

    public $lastExpr;

    public function __construct($table,$type = 'LEFT')
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
        $subexpr->builder = $this->builder;
        $this->onExpr[] = $subexpr;
        return $this->lastExpr = $subexpr->createExpr(null);
    }



    public function toSql()
    {
        $sql = ' ' . $this->type 
            . ' JOIN '
            . $this->table;

        if ( $this->alias ) {
            $sql = $sql . ' ' . $this->alias;
        }
        foreach( $this->onExpr as $expr ) {
            $sql .= ' ON ' . $expr->toSql();
        }
        return $sql;
    }
}


