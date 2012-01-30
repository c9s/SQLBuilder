<?php

namespace SQLBuilder;

class Expression
{
    public $builder;


    /* child */
    public $child;

    /* parent expression */
    public $parent;

    /* op code connects to parent expression */
    public $parentOp;

    /* is group ? ( ) */
    public $isGroup;

    public $cond;

    public function setCond($cond)
    {
        if( $this->cond ) {
            return $this->and()->setCond($cond);
        }
        $this->cond = $cond;
        return $this;
    }

    public function is($c,$n)
    {
        return $this->setCond( array( $c , 'is' , array($n) ));
    }

    public function isNot($c,$n)
    {
        return $this->setCond( array( $c , 'is not' , array($n) ));
    }

    public function isEqual($c,$n)
    {
        $this->setCond(array( $c , '=' , $n ));
        return $this;
    }

    public function isNotEqual($c,$n)
    {
        $this->setCond(array( $c, '!=' , $n ));
    }


    public function __call($method,$args)
    {
        switch( $method )
        {
            case 'and':
                return $this->newAnd();
                break;
            case 'or':
                return $this->newOr();
                break;
        }
    }

    public function createExpr($op = 'AND')
    {
        $subexpr = new self;
        $subexpr->parent = $this;
        $subexpr->parentOp = $op;
        $subexpr->builder = $this->builder;
        $this->child = $subexpr;
        return $subexpr;
    }

    public function newAnd()
    {
        return $this->createExpr('AND');
    }

    public function newOr()
    {
        return $this->createExpr('OR');
    }

    public function group($op = 'AND')
    {
        $subexpr = $this->createExpr($op);
        $subexpr->isGroup = true;
        return $subexpr;
    }

    public function ungroup()
    {
        return $this->parent;
    }

    public function back()
    {
        return $this->parent;
    }

    public function inflate()
    {
        $sql = '';

        if( $this->parent )
            $sql .= $this->parentOp . ' ';

        list($k,$op,$v) = $this->cond;
		if( $this->builder->driver->placeholder ) {
            if( is_array($v) ) {
                $sql .= $this->builder->driver->getQuoteColumn($k) . ' ' . $op . ' ' . $v[0];
            } else {
                if( is_integer($k) )
                    $k = $v;
                $sql .= $this->builder->driver->getQuoteColumn( $k ) . ' ' . $op . ' '  . $this->builder->getPlaceHolder($k);
            }
		}
		else {
            if( is_array($v) ) {
                $sql .= $this->builder->driver->getQuoteColumn($k) . ' ' . $op . ' ' . $v[0];
            } else {
                $sql .= $this->builder->driver->getQuoteColumn($k) . ' ' . $op . ' ' 
                    . '\'' . call_user_func( $this->builder->driver->escaper , $v ) . '\'';
            }
		}

        if( $this->child )
            $sql .= ' ' . $this->child->inflate();

        return $sql;
    }

    public function __toString()
    {

    }

}

