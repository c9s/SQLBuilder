<?php
namespace SQLBuilder;

class ExpressionGroup 
    extends Expression
{
    public function toSql()
    {
        $sql = '';
        if( $this->parentOp )
            $sql .= $this->parentOp . ' ';

        $sql .= '(';
        if( $this->childs ) {
            foreach( $this->childs as $child ) {
                $sql .= $child->toSql();
            }
        }
        $sql .= ')';
        return $sql;
    }

    public function setBuilder($builder) {
        $this->builder = $builder;
        foreach( $this->childs as $child ) {
            $child->setBuilder($builder);
        }
    }
}
