<?php
namespace SQLBuilder\ANSI;
use SQLBuilder\Universal\Expr\FuncCallExpr;
use SQLBuilder\Raw;
use SQLBuilder\Utils;

class ScalarFunction
{
    static public function UCASE($expr) 
    {
        return new FuncCallExpr('UCASE', Utils::buildFunctionArguments(array($expr)));
    }

    static public function LCASE($expr) 
    {
        return new FuncCallExpr('LCASE', Utils::buildFunctionArguments(array($expr)));
    }

    static public function MID($expr) 
    {
        return new FuncCallExpr('MID', Utils::buildFunctionArguments(array($expr)));
    }

    static public function LEN($expr) 
    {
        return new FuncCallExpr('LEN', Utils::buildFunctionArguments(array($expr)));
    }

    static public function ROUND($expr) 
    {
        return new FuncCallExpr('ROUND', Utils::buildFunctionArguments(array($expr)));
    }

    static public function NOW()
    {
        return new FuncCallExpr('NOW', array());
    }

    // SELECT FORMAT(column_name,format) FROM table_name;
    // TODO: $columnName can be another expr object
    static public function FORMAT($columnName, $format) 
    {
        return new FuncCallExpr('FORMAT', Utils::buildFunctionArguments(array($columnName, $format)));
    }

}

