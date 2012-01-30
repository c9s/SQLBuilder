<?php
namespace SQLBuilder;

class ExpressionGroup extends Expression
{
    public function inflate()
    {
        $sql = '';
        if( $this->parentOp )
            $sql .= $this->parentOp . ' ';

        $sql .= '(';
        if( $this->childs ) {
            foreach( $this->childs as $child ) {
                $sql .= $child->inflate();
            }
        }
        $sql .= ')';
        return $sql;
    }

}
