<?php
namespace SQLBuilder\ANSI;
use SQLBuilder\Universal\Expr\FuncCallExpr;
use SQLBuilder\Raw;

class AggregateFunction
{
    static public function AVG($expr) {
        return new FuncCallExpr('AVG', array(new Raw($expr)));
    }

    static public function COUNT($expr) {
        return new FuncCallExpr('COUNT', array(new Raw($expr)));
    }

    static public function MAX($expr) {
        return new FuncCallExpr('MAX', array(new Raw($expr)));
    }

    static public function MIN($expr) {
        return new FuncCallExpr('MIN', array(new Raw($expr)));
    }

    static public function SUM($expr) {
        return new FuncCallExpr('SUM', array(new Raw($expr)));
    }
}



