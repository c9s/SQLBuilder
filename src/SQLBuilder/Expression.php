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

class Expression
{
    public $driver;

    /* child */
    public $childs = array();

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

    public function equal($c,$n)
    {
        return $this->setCond(array( $c , '=' , $n ));
    }

    public function notEqual($c,$n)
    {
        return $this->setCond(array( $c, '!=' , $n ));
    }

    public function like($c,$n)
    {
        return $this->setCond(array( $c, 'like', $n ));
    }

    public function greater($c,$n)
    {
        return $this->setCond(array( $c, '>', $n ));
    }

    public function less($c,$n)
    {
        return $this->setCond(array( $c, '<', $n ));
    }

    public function group($op = 'AND')
    {
        if( ! $this->cond && count($this->childs) == 0 )
            $op = null;
        $groupExpr = $this->createGroupExpr($op);
        return $groupExpr->createExpr(null);
    }

    public function ungroup()
    {
        // back to Expression Group
        $p = $this;
        while( $p = $p->parent ) {
            if( is_a($p, 'SQLBuilder\ExpressionGroup') )
                return $p->parent;
        }
    }


    /**
     * back to top parent 
     */
    public function back()
    {
        $p = $this;
        while( $p = $p->parent ) {
            if( ! $p->parent )
                return $p;
        }
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
        $subexpr = new self;
        $subexpr->parent = $this;
        $subexpr->parentOp = $op;
        $subexpr->driver = $this->driver;
        $this->childs[] = $subexpr;
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


    public function inflate()
    {
        $sql = '';

        if( $this->parentOp )
            $sql .= $this->parentOp . ' ';

        if( $this->cond ) {
            list($k,$op,$v) = $this->cond;
            if( $this->driver->placeholder ) {
                $sql .= $this->driver->getQuoteColumn($k) . ' ' . $op . ' '  . $this->driver->getPlaceHolder($k);

                /*
                if( is_array($v) ) {
                    $sql .= $this->driver->getQuoteColumn($k) . ' ' . $op . ' ' . $v[0];
                } else {
                    $sql .= $this->driver->getQuoteColumn( $k ) . ' ' . $op . ' '  . $this->getPlaceHolder($k);
                }
                */
            }
            else {
                if( is_array($v) ) {
                    $sql .= $this->driver->getQuoteColumn($k) . ' ' . $op . ' ' . $v[0];
                } elseif( is_integer($v) ) {
                    $sql .= $this->driver->getQuoteColumn($k) . ' ' . $op . ' ' . $v;
                } else {
                    $sql .= $this->driver->getQuoteColumn($k) . ' ' . $op . ' ' 
                        . '\'' 
                        . $this->driver->escape($v)
                        . '\'';
                }
            }
        }

        if( $this->childs ) {
            foreach( $this->childs as $child ) {
                $sql .= ' '. $child->inflate();
            }
        }

        return $sql;
    }

    public function __toString()
    {
        return $this->inflate();
    }

}

