<?php
namespace SQLBuilder;
use Exception;

class Expression extends BaseExpression
{
    public $driver;

    /* child */
    public $childs = array();

    /* parent expression */
    public $parent;

    /* op code connects to parent expression */
    public $parentOp;

    public $op;

    public function setOp($op)
    {
        if( $this->op ) {
            return $this->and()->setOp($op);
        } else {
            $this->op = $op;
            return $this;
        }
    }

    public function is($c,$n)
    {
        return $this->setOp( array( $c , 'is' , array($n) ));
    }

    public function isNot($c,$n)
    {
        return $this->setOp( array( $c , 'is not' , array($n) ));
    }

    public function equal($c,$n)
    {
        return $this->setOp(array( $c , '=' , $n ));
    }

    public function notEqual($c,$n)
    {
        return $this->setOp(array( $c, '!=' , $n ));
    }

    public function like($c,$n)
    {
        return $this->setOp(array( $c, 'like', $n ));
    }

    public function greater($c,$n)
    {
        return $this->setOp(array( $c, '>', $n ));
    }

    public function less($c,$n)
    {
        return $this->setOp(array( $c, '<', $n ));
    }

    public function group($op = 'AND')
    {
        if( ! $this->op && count($this->childs) == 0 )
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

    public function between($column,$from,$to)
    {
        $expr = new BetweenExpression( $column, $from, $to );
        return $this;
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

    public function newAnd()
    {
        return $this->createExpr('AND');
    }

    public function newOr()
    {
        return $this->createExpr('OR');
    }


    public function toSql()
    {
        $sql = '';

        if( $this->parentOp )
            $sql .= $this->parentOp . ' ';

        if( $this->op ) {
            if( is_array( $this->op ) ) {

                list($k,$op,$v) = $this->op;
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
            elseif( is_object( $this->op ) ) {
                $sql .= $this->op->toSql();
            }
            else {
                throw new Exception( 'Unsupported Op type.' );
            }
        }

        if( $this->childs ) {
            foreach( $this->childs as $child ) {
                $sql .= ' '. $child->toSql();
            }
        }

        return $sql;
    }

    public function __toString()
    {
        return $this->toSql();
    }

}

