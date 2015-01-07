<?php
namespace SQLBuilder\ANSI;
use SQLBuilder\Universal\Expr\FuncCallExpr;
use SQLBuilder\Raw;

class ScalarFunction
{
    static public function ucase($expr) {
        return new FuncCallExpr('UCASE', array(new Raw($expr)));
    }

    static public function lcase($expr) {
        return new FuncCallExpr('LCASE', array(new Raw($expr)));
    }

    static public function mid($expr) {
        return new FuncCallExpr('MID', array(new Raw($expr)));
    }

    static public function len($expr) {
        return new FuncCallExpr('LEN', array(new Raw($expr)));
    }

    static public function round($expr) {
        return new FuncCallExpr('ROUND', array(new Raw($expr)));
    }

    static public function now($expr) {
        return new FuncCallExpr('NOW', array(new Raw($expr)));
    }

    // SELECT FORMAT(column_name,format) FROM table_name;
    // TODO: $columnName can be another expr object
    static public function format($columnName, $format) {
        return new FuncCallExpr('FORMAT', array(new Raw($columnName), $format));
    }

}

