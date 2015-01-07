<?php
namespace SQLBuilder\ANSI;
use SQLBuilder\Universal\Expr\FuncCallExpr;
use SQLBuilder\Raw;

class AggregateFunction
{
    static public function avg($expr) {
        return new FuncCallExpr('AVG', array(new Raw($expr)));
    }

    static public function count($expr) {
        return new FuncCallExpr('COUNT', array(new Raw($expr)));
    }

    static public function first($expr) {
        return new FuncCallExpr('FIRST', array(new Raw($expr)));
    }

    static public function last($expr) {
        return new FuncCallExpr('LAST', array(new Raw($expr)));
    }

    static public function max($expr) {
        return new FuncCallExpr('MAX', array(new Raw($expr)));
    }

    static public function min($expr) {
        return new FuncCallExpr('MIN', array(new Raw($expr)));
    }

    static public function sum($expr) {
        return new FuncCallExpr('SUM', array(new Raw($expr)));
    }
}



